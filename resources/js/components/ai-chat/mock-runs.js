import { reactive } from 'vue';

// Prototype: pretend the chat is driving Statamic the way the MCP does. Nothing here is real. It turns what you typed
// into a plan of tool calls, then plays them one after another so each card goes pending, running, done.
//
// Each step: the tool, the arguments it was called with, what it reported back, and how long it "runs" for (ms).

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

const scenarios = [
    {
        // "Create a new homepage image", "use this as the hero image..."
        match: /\b(image|photo|picture|hero|asset|banner)s?\b/i,
        plan: (text, who) => ({
            title: 'Update published entry',
            permission: 'publish entries',
            steps: [
                { tool: 'statamic.search_assets', args: 'container: assets · query: "homepage"', result: '4 matching images found', ms: 1000 },
                { tool: 'statamic.update_entry', args: 'entry: pages::en::home', result: 'hero_image updated · copy unchanged', ms: 1200 },
                { tool: 'statamic.publish_entry', args: `authorized_as: ${who.name} · permission checked`, result: 'Published content change committed', ms: 1100 },
            ],
            done: { text: 'The homepage has a new hero image' },
        }),
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
            { tool: 'statamic.update_asset', args: 'assets: 6 without alt · field: alt', result: 'Alt text written for 6 images', ms: 1600 },
        ],
        done: { text: 'Alt text added to 6 images', note: '3 already had some, and were left alone.' },
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

// Plays the run: each step goes running, waits, then done. onChange lets the thread keep itself scrolled to the bottom.
export async function playRun(run, onChange = () => {}) {
    for (const step of run.steps) {
        step.state = 'running';
        onChange();
        await wait(step.ms);
        step.state = 'done';
        onChange();
        await wait(250);
    }

    run.status = 'done';
    onChange();
}
