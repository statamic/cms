import { nextTick, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import useAiChanges from '@/composables/use-ai-changes.js';
import { cp_url } from '@/bootstrap/globals.js';

// Prototype: pretend the chat is driving Statamic the way the MCP does. Nothing here is real. It turns what you typed
// into a plan of tool calls, then plays them one after another so each card goes pending, running, done.
//
// Each step: the tool, the arguments it was called with, what it reported back, and how long it "runs" for (ms).
// A step can also have an `action`, which really happens once it has run, like opening an entry or filling in its fields.
// An action can ask you something (see ask()), and can stop the run by returning { stop: outcome }, plus cancelled: true if you called it off.


const slug = (text, words = 5) =>
    text
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, words)
        .join('-') || 'untitled';

const titleCase = (text) => text.replace(/\b\w/g, (c) => c.toUpperCase());

// A title for a new entry, taken from what was asked: "a blog post about our latest product" becomes "Our Latest Product"
const subjectOf = (text) => {
    const about = text.match(/\b(?:about|for|on)\s+(.+)$/i);
    return titleCase((about ? about[1] : text).replace(/[.!?]+$/, '').trim());
};

// The entry the edit scenario works on: the demo entry, with plain text fields to fill in
const summerSale = {
    title: 'Our summer sale',
    url: () => cp_url('collections/ai_suggestions/entries/7f3c2a10-ai00-4c1e-9b2a-sugg00000001'),
    values: {
        subtitle: 'Our biggest ever discounts on outdoor furniture',
        excerpt: 'Up to half off every outdoor furniture range, from loungers to dining sets, until Sunday.',
        meta_description: 'Up to 50% off outdoor furniture in our summer sale. Loungers, dining sets and more, until Sunday.',
    },
    labels: { subtitle: 'Subtitle', excerpt: 'Excerpt', meta_description: 'Meta Description' },
};

const scenarios = [
    {
        // "Tighten up the summer sale entry", "Fix the excerpt"... It changes the entry by filling in its form, and leaves saving to you.
        // If the entry isn't open, it opens it first (the chat stays put across the visit), so you see the changes where they'd be made.
        match: /\b(tighten|tidy|rewrite|improve|shorten|shorter|fix|typo|excerpt|subtitle|meta)\b/i,
        plan: () => {
            const { currentForm, nextForm, fill } = useAiChanges();
            const isOpen = window.location.href.startsWith(summerSale.url());
            const fields = Object.keys(summerSale.values);

            return {
                title: 'Edit entry',
                permission: 'edit entries',
                steps: [
                    ...(isOpen
                        ? []
                        : [
                              { tool: 'statamic.search_entries', args: 'query: "summer sale"', result: `1 entry found · ${summerSale.title}`, ms: 900 },
                              {
                                  tool: 'cp.open_entry',
                                  args: `entry: "${summerSale.title}"`,
                                  result: 'Opened in the editor',
                                  ms: 300,
                                  action: (ask) => openEntry(summerSale, ask, nextForm),
                              },
                          ]),
                    { tool: 'statamic.get_blueprint', args: 'collection: ai_suggestions · blueprint: article', result: '5 fields loaded', ms: 700 },
                    {
                        tool: 'cp.fill_fields',
                        args: `fields: ${fields.join(', ')}`,
                        result: 'Changed in the form · not saved',
                        ms: 1100,
                        action: () => currentForm() && fill(summerSale.values),
                    },
                ],
                done: {
                    text: `Changed ${fields.length} fields on ${summerSale.title}`,
                    items: fields.map((field) => summerSale.labels[field]),
                    note: 'Nothing’s saved yet. Check them over, then save or publish as usual.',
                },
            };
        },
    },
    {
        // "Create a blog post about our latest product"
        match: /\b(blog|post|article|news)\b/i,
        plan: (text) => ({
            title: 'Create draft entry',
            permission: 'create entries',
            steps: [
                { tool: 'statamic.get_blueprint', args: 'collection: blog · blueprint: article', result: '12 fields loaded', ms: 800 },
                { tool: 'statamic.create_entry', args: 'collection: blog · status: draft', result: `"${subjectOf(text)}" created as a draft`, ms: 1200 },
                { tool: 'statamic.update_entry', args: `entry: blog::en::${slug(subjectOf(text))}`, result: 'content written · seo_description set', ms: 1500 },
            ],
            done: { text: 'Saved as a draft, ready for review' },
        }),
    },
    {
        // "Create a landing page for the summer sale"
        match: /\b(landing|page|campaign|sale)\b/i,
        plan: (text) => ({
            title: 'Create draft page',
            permission: 'create pages',
            steps: [
                { tool: 'statamic.get_blueprint', args: 'collection: pages · blueprint: landing_page', result: '9 fields loaded', ms: 800 },
                { tool: 'statamic.create_entry', args: 'collection: pages · status: draft', result: `"${subjectOf(text)}" created as a draft`, ms: 1200 },
                { tool: 'statamic.update_entry', args: `entry: pages::en::${slug(subjectOf(text))}`, result: '3 blocks added · hero, features, call_to_action', ms: 1500 },
            ],
            done: { text: 'Landing page saved as a draft' },
        }),
    },
    {
        // Anything else is treated as looking something up
        match: /./,
        plan: (text) => ({
            title: 'Search content',
            permission: 'view entries',
            steps: [
                { tool: 'statamic.search_entries', args: `query: "${text.trim().slice(0, 40)}"`, result: '8 entries found', ms: 1000 },
                { tool: 'statamic.get_entry', args: 'entry: most relevant', result: 'Loaded for review', ms: 800 },
            ],
            done: { text: 'Found 8 matching entries' },
        }),
    },
];

function whoIsAsking() {
    const user = typeof Statamic !== 'undefined' ? Statamic.user : null;

    return {
        name: user?.name || user?.email || 'You',
        role: user?.super ? 'Super user' : 'Editor',
    };
}

export function planRun(text) {
    const who = whoIsAsking();
    const plan = scenarios.find((s) => s.match.test(text)).plan(text, who);

    return reactive({
        ...plan,
        who,
        status: 'working', // working, then done
        steps: plan.steps.map((step) => ({ ...step, state: 'pending' })), // pending, running, then done
    });
}

// The page the chat was asked about, from the browser tab's title ("Our summer sale ‹ Statamic")
const currentPage = () => document.title.split(/ [‹›] /)[0] || 'this page';

// What each of the example saved prompts (see AiSettingsController) pretends to do. Any others follow their instructions, vaguely.
const promptScenarios = {
    'legal-check': (page) => ({
        permission: 'view entries',
        steps: [
            { tool: 'statamic.get_entry', args: `entry: "${page}"`, result: 'Loaded · 1,180 words', ms: 1000 },
            { tool: 'statamic.get_blueprint', args: 'fields: content, terms', result: 'No terms field', ms: 700 },
        ],
        done: {
            text: '3 things to check before this goes live',
            items: [
                '“The lowest prices anywhere” needs evidence, or could say “low prices”.',
                '“Free returns” should link to the returns policy.',
                '“Ends Sunday” needs a date and time.',
            ],
            note: 'Nothing was changed.',
        },
    }),
    'alt-text': (page) => ({
        permission: 'edit assets',
        steps: [
            { tool: 'statamic.get_entry', args: `entry: "${page}" · fields: assets`, result: '9 images used', ms: 900 },
            { tool: 'statamic.get_assets', args: 'assets: 9 · fields: alt', result: '6 without alt text', ms: 1000 },
        ],
        // Alt text is saved on the image, not with this entry, so it can't wait for you to save the entry. It suggests rather than changes.
        done: {
            text: 'Alt text for 6 images',
            items: ['sale-hero.jpg: “A teak lounger on a sunny patio”', 'dining-set.jpg: “Six-seat outdoor dining set under a parasol”', 'hammock.jpg: “Striped hammock between two trees”', '…and 3 more'],
            note: 'Nothing was changed, as alt text saves on each image straight away.',
        },
    }),
    'seo-check': (page) => ({
        permission: 'view entries',
        steps: [{ tool: 'statamic.get_entry', args: `entry: "${page}" · fields: seo`, result: 'Loaded', ms: 1000 }],
        done: {
            text: '2 fixes suggested',
            items: ['The meta title is 72 characters. Search results cut it at about 60.', 'There’s no meta description. Try: “Everything in the summer sale, up to half off until Sunday.”'],
            note: 'Nothing was changed.',
        },
    }),
};

// A saved prompt, run on the page that's open. The run is titled with its name, so it's clear which one ran.
export function planPromptRun(prompt) {
    const page = currentPage();
    const scenario = promptScenarios[prompt.command] ?? (() => ({
        permission: 'view entries',
        steps: [{ tool: 'statamic.get_entry', args: `entry: "${page}"`, result: 'Loaded', ms: 1000 }],
        done: { text: `Done, following the “${prompt.name}” instructions` },
    }));
    const plan = scenario(page);

    return reactive({
        ...plan,
        title: prompt.name,
        who: whoIsAsking(),
        status: 'working',
        steps: plan.steps.map((step) => ({ ...step, state: 'pending' })),
    });
}

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Asks you something in the chat and waits for the answer. The chat shows `run.waiting` (where depends on the option) and calls its answer() with a choice's value.
function ask(run, step, question, onChange) {
    return new Promise((resolve) => {
        step.state = 'waiting';
        run.status = 'waiting';
        run.waiting = {
            ...question,
            answer: (value) => {
                run.waiting = null;
                step.state = 'running';
                run.status = 'working';
                onChange();
                resolve(value);
            },
        };
        onChange();
    });
}

// Waits for something to become true, checking every so often, and gives up after a while
async function until(check, timeout = 8000) {
    for (let waited = 0; waited < timeout; waited += 100) {
        if (check()) return true;
        await wait(100);
    }
    return check();
}

// Opens an entry so the chat can fill it in, with the chat staying put across the visit.
// If the page you're on has unsaved changes, it asks first, in place of the chat's input: save them, leave them, or stop.
async function openEntry(entry, ask, nextForm) {
    const page = currentPage();
    const stopped = { text: 'Stopped before opening ' + entry.title, note: `Nothing was changed, and your changes to “${page}” are still there.` };

    if (Statamic.$dirty.count()) {
        const choice = await ask({
            question: `Your changes to “${page}” aren’t saved yet. I need to open “${entry.title}” to make this change.`,
            choices: [
                { value: 'save', label: 'Save and continue' },
                { value: 'leave', label: 'Don’t save' },
                { value: 'cancel', label: 'Cancel' },
            ],
        });

        if (choice === 'cancel') return { stop: stopped, cancelled: true };

        if (choice === 'save') {
            // The same as pressing ⌘S / Ctrl+S on the page, so it saves the way the page normally does
            const mac = navigator.platform.toUpperCase().includes('MAC');
            document.dispatchEvent(new KeyboardEvent('keydown', { key: 's', code: 'KeyS', keyCode: 83, which: 83, metaKey: mac, ctrlKey: !mac, bubbles: true }));
            if (!(await until(() => !Statamic.$dirty.count()))) {
                return { stop: { text: `Couldn’t save “${page}”`, note: 'Nothing was changed. Check the form for errors, then ask again.' } };
            }
        }

        if (choice === 'leave') {
            Statamic.$dirty.names().forEach((name) => Statamic.$dirty.remove(name));
            Statamic.$dirty.disableWarning();
            await nextTick();
        }
    }

    // If something else stops the visit, there's no new form, so stop rather than fill in the page that's open.
    const opened = nextForm();
    let started = false;
    router.visit(entry.url(), { onStart: () => (started = true) });
    await wait(50);
    if (!started) return { stop: stopped };
    if (!(await opened)) return { stop: { text: 'Couldn’t open ' + entry.title, note: 'Nothing was changed.' } };
}

// Plays the run: each step goes running, waits, then done. onChange lets the thread keep itself scrolled to the bottom.
export async function playRun(run, onChange = () => {}) {
    for (const [i, step] of run.steps.entries()) {
        step.state = 'running';
        onChange();
        await wait(step.ms);

        const result = await step.action?.((question) => ask(run, step, question, onChange));
        if (result?.stop) {
            run.steps.slice(i).forEach((later) => (later.state = 'skipped'));
            run.done = result.stop;
            run.status = result.cancelled ? 'cancelled' : 'stopped';
            onChange();
            return;
        }

        step.state = 'done';
        onChange();
        await wait(250);
    }

    run.status = 'done';
    onChange();
}
