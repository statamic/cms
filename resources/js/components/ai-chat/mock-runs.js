import { nextTick, reactive, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import useAiChanges from '@/composables/use-ai-changes.js';
import { cp_url } from '@/bootstrap/globals.js';

// Prototype: pretend the chat is driving Statamic the way the MCP does. Nothing here is real. It turns what you typed
// into a plan of tool calls, then plays them one after another so each card goes pending, running, done.
//
// Each step: the tool, the arguments it was called with (and the same in words, as its detail), what it reported back, and how long it "runs" for (ms).
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

// "Subtitle, Excerpt and Meta Description"
const list = (items) => (items.length > 1 ? `${items.slice(0, -1).join(', ')} and ${items.at(-1)}` : items[0] ?? '');

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

// The homepage, for questions about it: a demo entry standing in for the site's real Home page
const homepage = {
    title: 'Home',
    url: () => cp_url('collections/ai_suggestions/entries/7f3c2a10-ai00-4c1e-9b2a-sugg00000002'),
};

// Where a new blog post is written: a new entry's form (the demo collection's, as its fields are plain text)
const newBlogPost = {
    title: 'New blog post',
    url: () => cp_url('collections/ai_suggestions/entries/create/default'),
};

// What a search finds: the demo entries, and the line in each that matched (shown in each one's tooltip)
const searchResults = [
    { title: summerSale.title, url: summerSale.url, text: 'The biggest discounts we have ever done on all of our outdoor furniture ranges this year' },
    { title: 'Home', url: () => cp_url('collections/ai_suggestions/entries/7f3c2a10-ai00-4c1e-9b2a-sugg00000002'), text: 'Handmade sofas, tables and outdoor furniture, delivered free across the UK.' },
];

// A blog post to add links to: a demo entry with a Bard field. Each paragraph is a list of bits of text, and the bits that become links
// say where to: the link itself, and the page's title and URL to show. The same paragraphs make up the post before (without the links) and after.
const gardenRange = {
    title: 'Our new garden range',
    url: () => cp_url('collections/ai_suggestions/entries/7f3c2a10-ai00-4c1e-9b2a-sugg00000003'),
    paragraphs: [
        ['Our new garden range is here, just in time for the ', ['summer sale', 'statamic://entry::7f3c2a10-ai00-4c1e-9b2a-sugg00000001', 'Our summer sale', '/our-summer-sale'], '. Every piece is handmade in our workshop in Devon from sustainably sourced oak.'],
        ['There are loungers, dining sets and benches, all designed to sit together. If you’re new to ', ['outdoor furniture', '/outdoor-furniture', 'Outdoor Furniture', '/outdoor-furniture'], ', our ', ['care guide', '/care-guide', 'Caring for Oak', '/care-guide'], ' explains how to keep oak looking its best year after year.'],
        ['As always, ', ['delivery is free across the UK', '/delivery', 'Delivery', '/delivery'], ', and you can see the whole range in our ', ['showrooms', '/showrooms', 'Showrooms', '/showrooms'], '.'],
    ],
};
const gardenRangeLinks = gardenRange.paragraphs.flat().filter(Array.isArray);

// The tooltip on a link it added: the page it goes to, then its URL in light grey (solid rather than faded, so it keeps its contrast on the
// dark tooltip). `link` is { to, url }.
const escape = (text) => text.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
export const linkTooltip = (link) => ({ content: `${escape(link.to)} <span class="text-gray-300">· ${escape(link.url)}</span>`, html: true });
// Where the words it linked go. Bard's content only has the link itself, so the page's title and URL come from here.
export const linkDetails = (text) => {
    const link = gardenRangeLinks.find((link) => link[0] === text);
    return link ? { to: link[2], url: link[3] } : null;
};
// The paragraphs as Bard's content, with the links in
const withLinks = (paragraphs) =>
    paragraphs.map((bits) => ({
        type: 'paragraph',
        content: bits.map((bit) =>
            Array.isArray(bit) ? { type: 'text', marks: [{ type: 'link', attrs: { href: bit[1], rel: null, target: null, title: null } }], text: bit[0] } : { type: 'text', text: bit },
        ),
    }));

// The About page in French, for the translating idea: written into a new entry's form (the demo collection's Blog Post blueprint, as for drafting)
const aboutInFrench = {
    title: 'À propos',
    url: () => cp_url('collections/ai_suggestions/entries/create/default?blueprint=blog_post'),
    paragraphs: [
        ['Nous sommes un atelier familial installé dans le Devon, où nous fabriquons à la main des meubles d’extérieur en chêne depuis 1998.'],
        ['Chaque pièce est conçue pour durer : du chêne issu de forêts gérées durablement, des assemblages traditionnels et une finition qui vieillit bien.'],
        ['Passez nous voir dans l’un de nos showrooms, ou écrivez-nous : nous serons ravis de vous aider à choisir.'],
    ],
};

// The Contact page, for the typo idea: a demo entry with a Bard field, holding the typo
const typoBefore = 'We’ll recieve your message and reply within two working days.';
const typoAfter = typoBefore.replace('recieve', 'receive');
const contactPage = {
    title: 'Contact',
    url: () => cp_url('collections/ai_suggestions/entries/7f3c2a10-ai00-4c1e-9b2a-sugg00000004'),
    paragraphs: [
        ['Got a question about an order, or want to arrange a visit? Send us a message using the form below.'],
        [typoBefore],
        ['If it’s urgent, ring the showroom on 01392 555 0142 and we’ll help straight away.'],
    ],
};

// The Blog blueprint's editor, for the idea of adding a field to it
const blogBlueprint = {
    title: 'Blog blueprint',
    url: () => cp_url('fields/blueprints/collections/blog/blog/edit'),
};

// The ideas from the research notes ("Ideas to mock up next"), each a scenario of its own. They come first, as their requests would
// otherwise be caught by the broader ones below ("typo" by the edit, "field" by the edit, "post" by the blog post).

// Next Monday, for "Publish it next Monday at 9am"
const nextMonday = () => {
    const date = new Date();
    date.setDate(date.getDate() + (((8 - date.getDay()) % 7) || 7));
    return date.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
};

// The time asked for, as 24-hour "9:00" or "14:30": "at 9am", "at 2.30pm", "at 14:00". Null if there isn't one.
const timeIn = (text) => {
    const match = text.match(/\bat\s+(\d{1,2})(?:[:.](\d{2}))?\s*(am|pm)?\b/i);
    if (!match) return null;
    let hour = Number(match[1]) % 12 + (match[3]?.toLowerCase() === 'pm' ? 12 : 0);
    if (!match[3] && Number(match[1]) >= 12) hour = Number(match[1]);
    return `${hour}:${match[2] ?? '00'}`;
};
// The time it was scheduled for, once you've said, for the outcome
const scheduled = reactive({ time: null });

// Whether the person can open Settings → AI, for the "No response" scenario. The settings page is for super users for now (there's no
// permission for it yet), but the two demos in the nav set this to show both outcomes (see AiChatDemos.vue).
export const canEditAi = ref(true);

const ideaScenarios = [
    {
        // "Which entries mention garden gnomes?". The search finds nothing, and says so, with a cross rather than a tick (see AiChatRun.vue).
        match: /\bgarden gnomes\b/i,
        plan: () => ({
            title: 'Search content',
            steps: [{ tool: 'statamic.search_entries', args: 'query: "garden gnomes"', detail: 'For “garden gnomes”', result: 'No entries found', ms: 1100 }],
            done: { text: 'No entries mention “garden gnomes”', note: 'Nothing was changed.', empty: true },
        }),
    },
    {
        // "Summarise our returns policy". The AI doesn't respond: the first step fails, and the run ends as failed.
        match: /\breturns policy\b/i,
        // Someone who can open Settings → AI gets the reason and a button to it; anyone else is sent to an administrator.
        plan: () => {
            const canSettings = canEditAi.value;

            return {
                title: 'No response',
                ends: 'failed',
                steps: [{ tool: 'statamic.search_entries', args: 'query: "returns policy"', detail: 'For “returns policy”', ms: 2500, fails: true }],
                done: {
                    text: 'The AI didn’t respond',
                    note: canSettings ? 'Nothing was changed. If this keeps happening, check Settings → AI.' : 'Nothing was changed. If this keeps happening, ask an administrator to check the AI settings.',
                    reason: canSettings ? 'Anthropic · request timed out after 30s' : null,
                    actions: [{ label: 'Try again', retry: true }, ...(canSettings ? [{ label: 'Open Settings → AI', url: () => cp_url('ai') }] : [])],
                },
            };
        },
    },
    {
        // 1 · "Fix the typo 'recieve' on the Contact page". It opens the page, then fixes the typo in its form, leaving saving to you (so the
        // live page stays as it is until you publish).
        match: /\brecieve\b|\btypo\b.*\bcontact\b/i,
        plan: () => {
            const { currentForm, nextForm, fill } = useAiChanges();
            const isOpen = window.location.href.startsWith(contactPage.url());
            // The page with the typo fixed
            const fixed = contactPage.paragraphs.map((bits) => bits.map((bit) => bit.replace('recieve', 'receive')));

            return {
                title: 'Fix a typo',
                steps: [
                    ...(isOpen
                        ? []
                        : [
                              { tool: 'statamic.search_entries', args: 'query: "recieve" · collection: pages', detail: 'For “recieve”, in Pages', result: `1 page found · ${contactPage.title}`, ms: 900 },
                              {
                                  tool: 'cp.open_entry',
                                  args: `entry: "${contactPage.title}"`,
                                  detail: contactPage.title,
                                  result: 'Opened in the editor',
                                  ms: 300,
                                  action: (ask, goingTo) => openEntry(contactPage, ask, nextForm, goingTo),
                              },
                          ]),
                    {
                        tool: 'cp.fill_fields',
                        args: 'fields: content · find: "recieve" · replace: "receive"',
                        detail: 'Content',
                        result: 'Changed in the form · not saved',
                        ms: 1100,
                        action: () => currentForm() && fill({ content: withLinks(fixed) }),
                    },
                ],
                done: {
                    text: 'Fixed “recieve” on Contact',
                    diff: { label: 'Content', before: typoBefore, after: typoAfter },
                    note: 'Nothing’s saved yet. Check it over, then save or publish as usual.',
                },
            };
        },
    },
    {
        // 3 · "Publish it next Monday at 9am". Publishing is never done without asking, so it stops for approval first.
        // Without a time ("Publish it next Monday"), it goes for 9:00, says so, and lets you change it in the question.
        match: /\b(publish|schedule)\b/i,
        plan: (text) => {
            const asked = timeIn(text);
            const day = nextMonday();
            const when = (time) => `${day} at ${time}`;

            return {
                title: 'Schedule publishing',
                steps: [
                    { tool: 'statamic.get_entry', args: 'entry: blog::our-new-opening-hours', detail: '“Our new opening hours”, in Blog', result: 'A draft · not live', ms: 800 },
                    {
                        tool: 'statamic.publish_entry',
                        args: `entry: blog::our-new-opening-hours · date: next Monday ${asked ?? '09:00'}`,
                        detail: asked ? `On ${when(asked)}` : `On ${day}, at 9:00 unless you say otherwise`,
                        result: '',
                        ms: 500,
                        action: async (ask, goingTo, step) => {
                            const answer = await ask({
                                question: asked
                                    ? `Publish “Our new opening hours” on ${when(asked)}? It’ll go live on the site then.`
                                    : `Publish “Our new opening hours” on ${day}? You didn’t say a time, so it’s 9:00. Change it if you like.`,
                                // Without a time asked for, the question has a time to change (see AiChat.vue), and answers with it
                                time: asked ? undefined : '09:00',
                                choices: [
                                    { value: 'schedule', label: 'Schedule it' },
                                    { value: 'cancel', label: 'Cancel' },
                                ],
                            });
                            const choice = answer?.value ?? answer;
                            if (choice === 'cancel') return { stop: { text: 'Didn’t schedule it', note: '“Our new opening hours” is still a draft.' }, cancelled: true };
                            scheduled.time = (answer?.time ?? asked ?? '09:00').replace(/^0(\d)/, '$1');
                            step.detail = `On ${when(scheduled.time)}`;
                            step.result = `Goes live on ${when(scheduled.time)}`;
                        },
                    },
                ],
                done: {
                    text: 'Scheduled “Our new opening hours”',
                    get items() {
                        return [`Goes live on ${when(scheduled.time)}`];
                    },
                    note: 'It stays a draft until then. You can change the date on the entry.',
                },
            };
        },
    },
    {
        // 4 · "Which images have no alt text?" A table to review: each image, and the alt text it suggests, to apply one by one or all at once.
        match: /\balt text\b|\bimages\b/i,
        plan: () => ({
            title: 'Find missing alt text',
            steps: [
                { tool: 'statamic.get_assets', args: 'container: assets · fields: alt', detail: 'Alt text on 16 images', result: '4 without alt text', ms: 1100 },
                { tool: 'statamic.describe_images', args: 'assets: 4', detail: 'The 4 images', result: 'Alt text suggested', ms: 1400 },
            ],
            done: {
                text: '4 images have no alt text',
                images: [
                    { file: 'golden-retriever.png', src: '/assets/golden-retriever.png', alt: 'A golden retriever standing and looking at the camera' },
                    { file: 'n64.jpg', src: '/assets/n64.jpg', alt: 'A boxed Nintendo 64 console and controller' },
                    { file: 'sega-dreamcast.jpg', src: '/assets/sega-dreamcast.jpg', alt: 'A Sega Dreamcast console lit in purple' },
                    { file: 'fanny-pack.webp', src: '/assets/fanny-pack.webp', alt: 'A bright pink, purple and teal bum bag' },
                ],
            },
        }),
    },
    {
        // 5 · "Translate the About page into French". Like drafting a post, it opens a new entry and writes the translation in the form, so you
        // see the page take shape, and leaves saving to you. (A real one would open the About page's French version, and fill that in.)
        match: /\btranslat/i,
        plan: () => {
            const { currentForm, nextForm, fill } = useAiChanges();

            return {
                title: 'Translate a page',
                steps: [
                    { tool: 'statamic.get_entry', args: 'entry: pages::about · site: default', detail: 'About, in English', result: 'Loaded · 640 words', ms: 900 },
                    {
                        tool: 'cp.new_entry',
                        args: 'collection: pages · site: french',
                        detail: 'The French version',
                        result: 'Opened in the editor',
                        ms: 300,
                        action: (ask, goingTo) => openEntry(aboutInFrench, ask, nextForm, goingTo),
                    },
                    {
                        tool: 'cp.fill_fields',
                        args: 'fields: title, content',
                        detail: 'Title and Content, in French',
                        result: `Title and ${aboutInFrench.paragraphs.length} paragraphs written · not saved`,
                        ms: 600,
                        // The title, then the content a paragraph at a time, as if it's being translated
                        action: async () => {
                            if (!currentForm()) return;
                            await fill({ title: 'À propos' });
                            for (let i = 1; i <= aboutInFrench.paragraphs.length; i++) await fill({ content: withLinks(aboutInFrench.paragraphs.slice(0, i)) }, 900);
                        },
                    },
                ],
                done: {
                    text: 'Translated About into French',
                    items: ['Title: “À propos”', `Content: ${aboutInFrench.paragraphs.length} paragraphs`],
                    note: 'Nothing’s saved yet. Check it over, then save it as a draft or publish it. The English page is unchanged.',
                },
            };
        },
    },
    {
        // 6 · "Summarise this week's contact form submissions". Read only: it changes nothing.
        match: /\bsummari[sz]e\b|\bsubmissions\b/i,
        plan: () => ({
            title: 'Summarise submissions',
            steps: [{ tool: 'statamic.get_submissions', args: 'form: contact_us · since: Monday', detail: 'Contact Us, this week', result: '23 submissions', ms: 1300 }],
            done: {
                text: '23 contact form submissions this week',
                items: ['9 delivery questions, mostly about the garden range', '6 asking about showroom opening times', '4 returns and refunds', '3 trade enquiries', '1 complaint about a late delivery, on Tuesday'],
                note: 'Nothing was changed.',
                actions: [{ label: 'View submissions', url: () => cp_url('forms/contact_us') }],
            },
        }),
    },
    {
        // 7 · "Add a subtitle field to the blog blueprint". The AI has the signed-in user's permissions and no more. If the user can change
        // blueprints, it opens the blueprint editor and adds the field there, leaving saving to you. If they can't, neither can it, and it says so.
        match: /\bblueprint\b|\badd an? \w+ field\b/i,
        plan: () => {
            const { nextBuilder, addBlueprintField } = useAiChanges();

            if (!Statamic.$permissions.has('configure fields')) {
                return {
                    title: 'Add a field',
                    ends: 'unable',
                    steps: [{ tool: 'statamic.get_blueprint', args: 'collection: blog · blueprint: blog', detail: 'Blog, in Blog', result: '5 fields loaded', ms: 800 }],
                    done: {
                        text: 'You don’t have permission to change blueprints',
                        note: 'I can only do what your role allows. An administrator can add a Subtitle field to the Blog blueprint.',
                    },
                };
            }

            return {
                title: 'Add a field',
                steps: [
                    { tool: 'statamic.get_blueprint', args: 'collection: blog · blueprint: blog', detail: 'Blog, in Blog', result: '5 fields loaded', ms: 800 },
                    {
                        tool: 'cp.open_blueprint',
                        args: 'collection: blog · blueprint: blog',
                        detail: 'Blog, in Blog',
                        result: 'Opened in the editor',
                        ms: 300,
                        action: (ask, goingTo) => openEntry(blogBlueprint, ask, nextBuilder, goingTo),
                    },
                    {
                        tool: 'cp.add_field',
                        args: 'handle: subtitle · type: text · after: title',
                        detail: 'A Subtitle field (Text), after Title',
                        result: 'Added in the editor · not saved',
                        ms: 1200,
                        action: () => addBlueprintField({ handle: 'subtitle', type: 'text', display: 'Subtitle' }),
                    },
                ],
                done: {
                    text: 'Added a Subtitle field to the Blog blueprint',
                    items: ['Subtitle: a Text field, after Title'],
                    note: 'Nothing’s saved yet. Check it over, then save the blueprint.',
                },
            };
        },
    },
];

const scenarios = [
    ...ideaScenarios,
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
                steps: [
                    ...(isOpen
                        ? []
                        : [
                              { tool: 'statamic.search_entries', args: 'query: "summer sale"', detail: 'For “summer sale”', result: `1 entry found · ${summerSale.title}`, ms: 900 },
                              {
                                  tool: 'cp.open_entry',
                                  args: `entry: "${summerSale.title}"`,
                                  detail: summerSale.title,
                                  result: 'Opened in the editor',
                                  ms: 300,
                                  action: (ask, goingTo) => openEntry(summerSale, ask, nextForm, goingTo),
                              },
                          ]),
                    { tool: 'statamic.get_blueprint', args: 'collection: ai_suggestions · blueprint: article', detail: 'Article, in AI Suggestions', result: '5 fields loaded', ms: 700 },
                    {
                        tool: 'cp.fill_fields',
                        args: `fields: ${fields.join(', ')}`,
                        detail: list(fields.map((field) => summerSale.labels[field])),
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
        // "Add links throughout the garden range blog post". It opens the post, finds pages it mentions, and links to them in its content,
        // leaving saving to you. (Before the blog scenario, as "post" would match that.)
        match: /\blinks?\b/i,
        plan: () => {
            const { currentForm, nextForm, fill } = useAiChanges();
            const isOpen = window.location.href.startsWith(gardenRange.url());

            return {
                title: 'Add links',
                steps: [
                    ...(isOpen
                        ? []
                        : [
                              { tool: 'statamic.search_entries', args: 'query: "garden range" · collection: blog', detail: 'For “garden range”, in Blog', result: `1 entry found · ${gardenRange.title}`, ms: 900 },
                              {
                                  tool: 'cp.open_entry',
                                  args: `entry: "${gardenRange.title}"`,
                                  detail: gardenRange.title,
                                  result: 'Opened in the editor',
                                  ms: 300,
                                  action: (ask, goingTo) => openEntry(gardenRange, ask, nextForm, goingTo),
                              },
                          ]),
                    { tool: 'statamic.get_entry', args: `entry: "${gardenRange.title}" · field: content`, detail: 'Its content', result: `${gardenRange.paragraphs.length} paragraphs read`, ms: 800 },
                    {
                        tool: 'statamic.search_entries',
                        args: `query: ${gardenRangeLinks.map((link) => `"${link[0]}"`).join(', ')}`,
                        detail: 'Pages the post mentions',
                        result: `${gardenRangeLinks.length} pages to link to`,
                        ms: 1200,
                    },
                    {
                        tool: 'cp.fill_fields',
                        args: 'fields: content',
                        detail: 'Content',
                        result: `${gardenRangeLinks.length} links added · not saved`,
                        ms: 1000,
                        action: () => currentForm() && fill({ content: withLinks(gardenRange.paragraphs) }),
                    },
                ],
                done: {
                    text: `Added ${gardenRangeLinks.length} links to “${gardenRange.title}”`,
                    links: gardenRangeLinks.map((link) => ({ text: link[0], to: link[2], url: link[3] })),
                    note: 'Nothing’s saved yet. Check them over, then save or publish as usual.',
                },
            };
        },
    },
    {
        // "How can I update the homepage?" It finds the page and takes you there, then says which fields do what, and offers to make the change.
        match: /\bhome\s?page\b/i,
        plan: () => {
            const { nextForm } = useAiChanges();
            const isOpen = window.location.href.startsWith(homepage.url());

            return {
                title: 'Find the homepage',
                steps: [
                    ...(isOpen
                        ? []
                        : [
                              { tool: 'statamic.search_entries', args: 'query: "home" · collection: pages', detail: 'For “home”, in Pages', result: `1 page found · ${homepage.title}`, ms: 900 },
                              {
                                  tool: 'cp.open_entry',
                                  args: `entry: "${homepage.title}"`,
                                  detail: homepage.title,
                                  result: 'Opened in the editor',
                                  ms: 300,
                                  action: (ask, goingTo) => openEntry(homepage, ask, nextForm, goingTo),
                              },
                          ]),
                    { tool: 'statamic.get_blueprint', args: 'collection: pages · blueprint: home', detail: 'Home, in Pages', result: '4 fields loaded', ms: 700 },
                ],
                done: {
                    text: 'This is your homepage. Its main fields:',
                    items: ['Title: the big heading at the top', 'Subtitle: the line under it', 'Excerpt: the short summary in listings', 'Meta Description: what shows in Google'],
                    note: 'Change what you need, then save or publish as usual. Or tell me what it should say, and I’ll fill it in for you.',
                },
            };
        },
    },
    {
        // "Create a blog post about our new garden range". It opens a new entry and writes it in the form, and leaves saving to you,
        // the same as when it edits one.
        match: /\b(blog|post|article|news)\b/i,
        plan: (text) => {
            const { currentForm, nextForm, fill } = useAiChanges();
            const subject = subjectOf(text);
            const about = subject.toLowerCase();
            const values = {
                title: subject,
                subtitle: `A first look at ${about}`,
                excerpt: `What’s new in ${about}, what it’s made from, and where to see it for yourself.`,
                meta_description: `${subject}: a first look at what’s new, what it’s made from and where to see it.`,
            };
            const fields = Object.keys(values);
            const labels = { title: 'Title', ...summerSale.labels };

            return {
                title: 'Write a blog post',
                steps: [
                    { tool: 'statamic.get_blueprint', args: 'collection: blog · blueprint: article', detail: 'Article, in Blog', result: '5 fields loaded', ms: 800 },
                    {
                        tool: 'cp.new_entry',
                        args: 'collection: blog · blueprint: article',
                        detail: 'An Article, in Blog',
                        result: 'Opened in the editor',
                        ms: 300,
                        action: (ask, goingTo) => openEntry(newBlogPost, ask, nextForm, goingTo),
                    },
                    {
                        tool: 'cp.fill_fields',
                        args: `fields: ${fields.join(', ')}`,
                        detail: list(fields.map((field) => labels[field])),
                        result: 'Written in the form · not saved',
                        ms: 1600,
                        action: () => currentForm() && fill(values),
                    },
                ],
                done: {
                    text: `Wrote “${subject}”`,
                    items: fields.map((field) => labels[field]),
                    note: 'Nothing’s saved yet. Check it over, then save it as a draft or publish it.',
                },
            };
        },
    },
    {
        // "Create a landing page for the summer sale"
        match: /\b(landing|page|campaign|sale)\b/i,
        plan: (text) => ({
            title: 'Create draft page',
            steps: [
                { tool: 'statamic.get_blueprint', args: 'collection: pages · blueprint: landing_page', detail: 'Landing Page, in Pages', result: '9 fields loaded', ms: 800 },
                { tool: 'statamic.create_entry', args: 'collection: pages · status: draft', detail: 'A draft in Pages', result: `"${subjectOf(text)}" created as a draft`, ms: 1200 },
                { tool: 'statamic.update_entry', args: `entry: pages::en::${slug(subjectOf(text))}`, detail: `“${subjectOf(text)}” in Pages`, result: '3 blocks added · hero, features, call_to_action', ms: 1500 },
            ],
            done: { text: 'Landing page saved as a draft' },
        }),
    },
    {
        // Anything else is treated as looking something up: "Which entries mention outdoor furniture?"
        // It answers with the entries it found, each a link to open it.
        match: /./,
        plan: (text) => {
            const query = (text.match(/\b(?:mention|mentions|about|with|containing|for)\s+(.+?)[?.!]*$/i)?.[1] ?? text.replace(/[?.!]+$/, '')).trim().slice(0, 40);
            const found = searchResults;

            return {
                title: 'Search content',
                steps: [
                    { tool: 'statamic.search_entries', args: `query: "${query}"`, detail: `For “${query}”`, result: `${found.length} entries found`, ms: 1000 },
                    { tool: 'statamic.get_entries', args: `entries: ${found.map((result) => result.title).join(', ')}`, detail: list(found.map((result) => result.title)), result: 'Read where each mentions it', ms: 900 },
                ],
                done: {
                    text: `${found.length} entries mention “${query}”`,
                    results: found,
                    // The names are links already, so it just offers the next step
                    note: 'Want me to change any of them?',
                },
            };
        },
    },
];

export function planRun(text) {
    const plan = scenarios.find((s) => s.match.test(text)).plan(text);

    return reactive({
        ...plan,
        status: 'working', // working, then done
        steps: plan.steps.map((step) => ({ ...step, state: 'pending' })), // pending, running, then done
    });
}

// The page the chat was asked about, from the browser tab's title ("Our summer sale ‹ Statamic")
const currentPage = () => document.title.split(/ [‹›] /)[0] || 'this page';
// The icon the Control Panel's nav shows for the section you're in (like Navigation, or Assets), so the trail can show the same one
const currentNavIcon = () => usePage().props._statamic?.nav?.flatMap((section) => section.items).find((item) => item.active)?.icon ?? null;

// What each of the example saved prompts (see AiSettingsController) pretends to do. Any others follow their instructions, vaguely.
const promptScenarios = {
    'legal-check': (page) => ({
        steps: [
            { tool: 'statamic.get_entry', args: `entry: "${page}"`, detail: `“${page}”`, result: 'Loaded · 1,180 words', ms: 1000 },
            { tool: 'statamic.get_blueprint', args: 'fields: content, terms', detail: 'Its Content and Terms fields', result: 'No terms field', ms: 700 },
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
        steps: [
            { tool: 'statamic.get_entry', args: `entry: "${page}" · fields: assets`, detail: `The images on “${page}”`, result: '9 images used', ms: 900 },
            { tool: 'statamic.get_assets', args: 'assets: 9 · fields: alt', detail: 'Alt text on 9 images', result: '6 without alt text', ms: 1000 },
        ],
        // Alt text is saved on the image, not with this entry, so it can't wait for you to save the entry. It suggests rather than changes.
        done: {
            text: 'Alt text for 6 images',
            items: ['sale-hero.jpg: “A teak lounger on a sunny patio”', 'dining-set.jpg: “Six-seat outdoor dining set under a parasol”', 'hammock.jpg: “Striped hammock between two trees”', '…and 3 more'],
            note: 'Nothing was changed, as alt text saves on each image straight away.',
        },
    }),
    'seo-check': (page) => ({
        steps: [{ tool: 'statamic.get_entry', args: `entry: "${page}" · fields: seo`, detail: `The SEO fields on “${page}”`, result: 'Loaded', ms: 1000 }],
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
        steps: [{ tool: 'statamic.get_entry', args: `entry: "${page}"`, detail: `“${page}”`, result: 'Loaded', ms: 1000 }],
        done: { text: `Done, following the “${prompt.name}” instructions` },
    }));
    const plan = scenario(page);

    return reactive({
        ...plan,
        title: prompt.name,
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
            // A question with a time to change (see AiChat.vue) answers with it too: { value, time }
            answer: (value, time) => {
                run.waiting = null;
                step.state = 'running';
                run.status = 'working';
                onChange();
                resolve(time === undefined ? value : { value, time });
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

// Where the chat is taking you, while the page changes (see AiChatMoveHint.vue)
// arrivedAt notes when it got there, so the header message can say you have arrived (see AiChatMoveHint.vue)
export const moving = reactive({ title: null, arrivedAt: null });

// Whether the chat is filling in the page's fields right now. The page is locked meanwhile, so you can't type over it or save it half
// written (see PanelLayout.vue), and the global header says so (see AiChatMoveHint.vue).
export const writing = reactive({ active: false });

// Opens an entry so the chat can fill it in, with the chat staying put across the visit.
// If the page you're on has unsaved changes, it asks first, in place of the chat's input: save them, leave them, or stop.
// goingTo puts the new page on the chat's trail as soon as it has opened, so its dot sets off straight away (see AiChatPageMarker.vue).
async function openEntry(entry, ask, nextForm, goingTo = () => {}) {
    const to = () => ({ title: entry.title, url: new URL(entry.url(), window.location.origin).pathname, icon: currentNavIcon() });
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

    // Fade the page back, with just long enough to see it start before the page changes. This is the only time the move adds, so keep it short.
    moving.title = entry.title;
    await wait(400);

    // If something else stops the visit, there's no new form, so stop rather than fill in the page that's open.
    const opened = nextForm();
    let started = false;
    router.visit(entry.url(), { onStart: () => (started = true) });
    await wait(50);
    if (!started) return (moving.title = null), { stop: stopped };
    if (!(await opened)) return (moving.title = null), { stop: { text: 'Couldn’t open ' + entry.title, note: 'Nothing was changed.' } };
    goingTo(to());
    moving.arrivedAt = Date.now();
    // The new page is open. The blur stays over it for a moment, so you see it arrive, then lifts. The chat carries on underneath
    // rather than waiting for it: a real model takes this long to pick its next step anyway, so it adds no time.
    setTimeout(() => moving.title === entry.title && (moving.title = null), 800);
}

// Plays the run: each step goes running, waits, then done. onChange lets the thread keep itself scrolled to the bottom.
// shownAt and endedAt note when each card first appears, so it only opens up then, not every time the chat is drawn again (see AiChatRun.vue).
export async function playRun(run, onChange = () => {}) {
    for (const [i, step] of run.steps.entries()) {
        // Filling in fields locks the page until they're done
        writing.active = ['cp.fill_fields', 'cp.add_field'].includes(step.tool);
        step.state = 'running';
        step.shownAt = Date.now();
        // The page it happened on, so the cards can show the way you've been taken, and get you back
        step.page = step.page?.url === window.location.pathname ? step.page : { title: currentPage(), url: window.location.pathname, icon: currentNavIcon() };
        onChange();
        await wait(step.ms);

        // A step that moves you puts the new page on the trail as it opens, on the next step, rather than waiting for that step to start
        const goingTo = (page) => {
            const next = run.steps[i + 1];
            if (!next) return;
            next.page = page;
            next.pageAt = Date.now();
            onChange();
        };
        // The AI never answered (see the "No response" scenario)
        if (step.fails) {
            step.state = 'failed';
            writing.active = false;
            run.status = 'failed';
            run.endedAt = Date.now();
            run.steps.slice(i + 1).forEach((later) => (later.state = 'skipped'));
            onChange();
            return;
        }

        const result = await step.action?.((question) => ask(run, step, question, onChange), goingTo, step);
        writing.active = false;
        if (result?.stop) {
            run.steps.slice(i).forEach((later) => {
                later.shownAt ??= Date.now();
                later.state = 'skipped';
            });
            run.done = result.stop;
            run.status = result.cancelled ? 'cancelled' : 'stopped';
            run.endedAt = Date.now();
            onChange();
            return;
        }

        step.state = 'done';
        onChange();
        await wait(250);
    }

    // Usually done, but a run can end up unable to do what was asked (see the blueprint idea)
    run.status = run.ends ?? 'done';
    run.endedAt = Date.now();
    onChange();
}
