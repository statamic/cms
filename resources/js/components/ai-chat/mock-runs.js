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
