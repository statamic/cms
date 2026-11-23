<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import useAiChat from '@/composables/use-ai-chat.js';
import { cp_url } from '@/bootstrap/globals.js';
import { suggestionsEmpty, suggestionsFail, suggestionsSlow } from '@/components/ai-suggest/use-ai-suggest.js';
import { canEditAi, pretendSave } from './mock-runs.js';

// Prototype: buttons at the bottom of the nav that play out a request in the AI chat, for demoing the branch. Each starts a new chat and
// types the request in, as if you had. What happens is up to the mock runs (see mock-runs.js). Phase 2's are things for later, which
// only show in the chat while one of them is being demoed (see phaseTwo in use-ai-chat.js).
const { demo, close } = useAiChat();

// The demos are at the bottom of the nav, so once one is clicked, jump the nav back to the top, so it looks as it normally would.
// The demo starts a couple of seconds later, so you see the page settle first. Clicking another in the meantime starts that one instead.
let starting = null;

// The demo played last, marked with a purple dot in the nav so you can see where you are in the list
const lastPlayed = ref(null);

// Scrolling back up shows the nav's scrollbar, which flashes in and out. So it's hidden for the length of the scroll, and back to normal after
// (.ai-demos-scrolling in components/ai.css).
function hideScrollbarWhileScrolling(nav) {
    const done = () => nav.classList.remove('ai-demos-scrolling');
    nav.classList.add('ai-demos-scrolling');
    nav.addEventListener('scrollend', done, { once: true });
    setTimeout(done, 1000);
    nav.scrollTo({ top: 0 });
    // Already at the top, so there's no scroll to wait for
    if (nav.scrollTop === 0) done();
}
// Whether clicking a demo jumps the nav back to the top. Off to start with, so you keep your place in the list while you try one after another.
// A switch at the very bottom of the nav turns it on, and it's remembered.
const scrollToTop = ref((() => {
    try {
        return localStorage.getItem('ai-demos-scroll-to-top') === 'on';
    } catch {
        return false;
    }
})());
function toggleScrollToTop() {
    scrollToTop.value = !scrollToTop.value;
    try {
        localStorage.setItem('ai-demos-scroll-to-top', scrollToTop.value ? 'on' : 'off');
    } catch {}
}

function play(event, item) {
    const nav = event.currentTarget.closest('.nav-main');
    if (nav && scrollToTop.value) hideScrollbarWhileScrolling(nav);
    lastPlayed.value = item.label;
    if (item.canEditAi !== undefined) canEditAi.value = item.canEditAi;
    clearTimeout(starting);
    starting = setTimeout(() => (item.run ? runOnPage(item.run) : demo(item.text, { dictate: item.dictate, phase: item.phase, create: item.create })), 2000);
}

// A demo with a run is not a chat request: it mocks you using the page. The chat is put away, and the run acts on the real page.
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
async function find(selector, timeout = 8000) {
    for (let waited = 0; waited < timeout; waited += 100) {
        const element = document.querySelector(selector);
        if (element) return element;
        await wait(100);
    }
    return null;
}
function runOnPage(run) {
    close();
    run({ wait, find });
}

// Field suggestions: go to the summer sale entry, then click the Subtitle field's suggest icon, as you would
const summerSaleUrl = cp_url('collections/ai_suggestions/entries/7f3c2a10-ai00-4c1e-9b2a-sugg00000001');
async function suggestSubtitle(helpers, fails = false, empty = false, slow = false) {
    return suggestField(helpers, 'subtitle', fails, empty, slow);
}

async function suggestField({ wait, find }, handle, fails = false, empty = false, slow = false) {
    if (!window.location.href.startsWith(summerSaleUrl)) router.visit(summerSaleUrl);
    // The field's id is on its input, so find that, then the suggest button beside it: the nearest parent that has one
    const input = await find(`[id^="field_"][id$="_${handle}"]`);
    let parent = input;
    while (parent && !parent.querySelector('button[aria-label="Suggest"]')) parent = parent.parentElement;
    const button = parent?.querySelector('button[aria-label="Suggest"]');
    if (!button) return;
    await wait(1200);
    // Another suggestions demo may have left its dropdown open, and clicking would close it. So close it first, and open it afresh.
    if (button.getAttribute('aria-expanded') === 'true') {
        button.click();
        await wait(400);
    }
    // How this round goes. They're put back to normal when the dropdown closes (see AiSuggest.vue), so suggesting by hand afterwards works.
    suggestionsSlow.value = slow;
    suggestionsFail.value = fails;
    suggestionsEmpty.value = empty;
    button.focus();
    button.click();
}

// The same, but the suggestions come back as a failure
const suggestSubtitleFails = (helpers) => suggestSubtitle(helpers, true);

// And the same again, but nothing comes back
const suggestSubtitleEmpty = (helpers) => suggestSubtitle(helpers, false, true);

// And again, but they take a long time
const suggestSubtitleSlow = (helpers) => suggestSubtitle(helpers, false, false, true);

// A Bard field's suggestions, which open over the entry's sidebar rather than under the field
const suggestBody = (helpers) => suggestField(helpers, 'body');

// Image demos, in the asset editor: one of the demo site's elephant photos (it has a cut-out for Remove background), opened as you would
// from Assets. Each starts with the image's AI card closed and empty, so they can be played in any order, and again.
const elephantUrl = cp_url('assets/browse/assets/carousel/mylon-ollila-j4ocwyap_cs-unsplash.jpg/edit');
// A button by the text it shows (innerText, as textContent would include its icon's hidden SVG title), or by its first line, for the list of
// skills, whose buttons go on to the start of the instructions
const buttonWithText = (text, within = document) =>
    [...(within ?? document).querySelectorAll('button')].find((button) => button.innerText.trim() === text || button.innerText.trim().split('\n')[0] === text);

async function findButton(text, within, timeout = 8000) {
    for (let waited = 0; waited < timeout; waited += 100) {
        const button = buttonWithText(text, typeof within === 'function' ? within() : within);
        if (button) return button;
        await wait(100);
    }
    return null;
}

// Types into a text box a letter at a time, as you would, so Vue's v-model sees each one
async function typeInto(box, text) {
    const setValue = Object.getOwnPropertyDescriptor(Object.getPrototypeOf(box), 'value').set;
    box.focus();
    for (const letter of text) {
        setValue.call(box, box.value + letter);
        box.dispatchEvent(new Event('input', { bubbles: true }));
        await wait(45);
    }
}

const press = (element, key, options = {}) => element.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true, cancelable: true, ...options }));
const card = () => document.querySelector('[data-ai-image-card]');

// Moves focus as Tab would, a stop at a time, with the focus ring showing (focusVisible, as the demo's started with a click), so you can
// see where it's going. It goes through the controls in the page's order, from what has focus now to the target.
async function tabTo(target, within) {
    const stops = [...within.querySelectorAll('button, textarea, input')].filter((element) => element.offsetParent && !element.disabled);
    const to = stops.indexOf(target);
    let from = stops.indexOf(document.activeElement);
    if (to < 0) return;
    if (from < 0 || from > to) from = to - 1;
    for (let i = from + 1; i <= to; i++) {
        stops[i].focus({ focusVisible: true });
        await wait(450);
    }
}

// Enter on a focused button presses it. A made-up Enter doesn't, so it's clicked, as Enter would.
async function pressEnter(button) {
    await wait(250);
    button.click();
}
const cardInput = () => card()?.querySelector('textarea');

// The elephant open in the asset editor
async function openElephant() {
    if (!window.location.href.startsWith(elephantUrl) || !buttonWithText('Edit with AI')) router.visit(elephantUrl);
    const askAi = await findButton('Edit with AI');
    if (!askAi) return null;
    // Suggest a filename leaves Rename's dialog open, so cancel it
    const renaming = [...document.querySelectorAll('[role=dialog]')].find((dialog) => dialog.innerText.startsWith('Rename'));
    if (renaming) {
        buttonWithText('Cancel', renaming)?.click();
        await wait(400);
    }
    return askAi;
}

// …with its AI card open and empty. A card that's already open (from the demo before) is cleared with Cancel, as you would, rather than
// closed and opened again; a closed one is opened with Edit with AI.
async function openImageCard() {
    const askAi = await openElephant();
    if (!askAi) return null;
    if (cardInput()) {
        buttonWithText('Cancel', card())?.click();
        const box = cardInput();
        Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value').set.call(box, '');
        box.dispatchEvent(new Event('input', { bubbles: true }));
        box.focus();
        await wait(500);
        return card();
    }
    await wait(900);
    // Tabbed to from Rename, beside it, then pressed
    const rename = buttonWithText('Rename');
    if (rename) {
        rename.focus({ focusVisible: true });
        await wait(450);
    }
    askAi.focus({ focusVisible: true });
    await pressEnter(askAi);
    await find('[data-ai-image-card] textarea');
    await wait(900);
    return card();
}

// Runs a skill as you would from the keyboard: Tab from the input along the pills to its own, then Enter. One past the first three is in
// the list "+ N" opens, so Tab goes to that, Enter opens it, and the arrow keys go down the list to it. It leaves what comes back for
// you to hover.
async function runImageSkill(name) {
    const wrapper = await openImageCard();
    if (!wrapper) return;
    const pill = buttonWithText(name, wrapper);
    if (pill) {
        await tabTo(pill, wrapper);
        return pressEnter(pill);
    }
    const more = [...wrapper.querySelectorAll('button')].find((button) => button.innerText.trim().startsWith('+'));
    if (!more) return;
    await tabTo(more, wrapper);
    await pressEnter(more);
    // The list opens with focus back in the input, where Up and Down go through it
    await wait(700);
    const names = [...wrapper.querySelectorAll('button')].filter((button) => button.offsetParent).map((button) => button.innerText.trim().split('\n')[0]);
    const steps = names.indexOf(name) + 1;
    for (let i = 0; i < steps; i++) {
        press(cardInput(), 'ArrowDown');
        await wait(450);
    }
    press(cardInput(), 'Enter');
}

// Edit with AI, then its skill pills fade in. An open card is closed first (Esc), so you see it open.
async function openAskAi() {
    if (!(await openElephant())) return;
    if (cardInput()) {
        press(cardInput(), 'Escape');
        await wait(600);
    }
    await openImageCard();
}

// The upscales only show before and after while one's hovered, so once they're back, the first is shown that way
async function upscale() {
    await runImageSkill('Upscale');
    await wait(2400);
    card()?.querySelector('.group.relative')?.dispatchEvent(new MouseEvent('mouseenter'));
}

// Erase something: picked from the list, then a stroke painted over the left of the image, then Erase
async function eraseSomething() {
    await runImageSkill('Erase something');
    const canvas = await find('canvas.cursor-crosshair', 3000);
    if (!canvas) return;
    await wait(800);
    const rect = canvas.getBoundingClientRect();
    const at = (x, y, type) => canvas.dispatchEvent(new PointerEvent(type, { bubbles: true, pointerId: 1, clientX: rect.left + rect.width * x, clientY: rect.top + rect.height * y }));
    at(0.22, 0.5, 'pointerdown');
    for (let step = 0; step <= 24; step++) {
        at(0.22 + (step / 24) * 0.14, 0.5 + Math.sin(step / 3) * 0.06, 'pointermove');
        await wait(30);
    }
    at(0.36, 0.5, 'pointerup');
    await wait(700);
    // Painting leaves focus in the input, so Tab goes from there to Erase
    const erase = await findButton('Erase', card(), 2000);
    if (!erase) return;
    await tabTo(erase, card());
    await pressEnter(erase);
}

// Create a skill, as in the chat: a /command that matches nothing offers to make it, Enter opens the form named after it, the instructions
// are typed in, then Tab to Save and Enter. The save is only pretend (pretendSave), so nothing is left in Settings → AI, and the skill an
// earlier play made is taken away first. Once it's saved, its command is in the input, and Enter runs it.
const page = usePage();
async function createImageSkill() {
    page.props._statamic.aiImagePrompts = (page.props._statamic.aiImagePrompts ?? []).filter((skill) => !skill.demo);
    if (!(await openImageCard())) return;
    await typeInto(cardInput(), '/moody-black-and-white');
    await wait(1200);
    press(cardInput(), 'Enter');
    const instructions = await find('[data-ai-image-card] textarea:not([aria-label])', 2000);
    if (!instructions) return;
    await wait(600);
    await typeInto(instructions, 'Black and white, with deep shadows and strong contrast.');
    await wait(600);
    const save = buttonWithText('Save', card());
    if (!save) return;
    await tabTo(save, card());
    pretendSave.value = true;
    await pressEnter(save);
    await find('[data-ai-image-card] textarea[aria-label]:not(:placeholder-shown)', 3000);
    pretendSave.value = false;
    await wait(1200);
    press(cardInput(), 'Enter');
}

// The asset editor's Alt Text field, written by the AI (with Added by AI and Undo once it's in)
async function writeAltText() {
    if (!(await openElephant())) return;
    const button = await find('button[aria-label="Write alt text"]');
    button?.scrollIntoView({ block: 'center', behavior: 'smooth' });
    await wait(700);
    button?.click();
}

// Rename, then the AI suggests a filename that says what's in it
async function suggestFilename() {
    if (!(await openElephant())) return;
    (await findButton('Rename'))?.click();
    const button = await find('button[aria-label="Suggest a filename"]');
    await wait(800);
    button?.click();
}

// Grouped, as the list got long. Each group's demos keep their order from before.
const groups = [
    {
        // The chat answers, and changes nothing
        title: 'Ask a question',
        demos: [
            { label: 'Update homepage', text: 'How can I update the homepage?' },
            { label: 'Find content', text: 'Which entries mention outdoor furniture?' },
            { label: 'Unpublished stories', text: 'Show me all my unpublished stories' },
            { label: '100 unpublished', text: 'Show me all my unpublished news articles' },
            // The ideas from the research notes (see ideaScenarios in mock-runs.js)
            { label: 'Find missing alt text', text: 'Which images have no alt text?' },
            { label: 'Summarise submissions', text: "Summarise this week's contact form submissions" },
        ],
    },
    {
        // The chat changes something, and leaves saving to you
        title: 'Make a change',
        demos: [
            { label: 'Fix summer sale', text: 'Tighten up the summer sale entry' },
            { label: 'Write a blog post', text: 'Create a blog post about our new garden range' },
            { label: 'Add links to a post', text: 'Add links throughout the garden range blog post' },
            { label: 'Add a field', text: 'Add a subtitle field to the blog blueprint' },
            { label: 'Fix a typo', text: "Fix the typo 'recieve' on the Contact page" },
            { label: 'Schedule publishing', text: 'Publish it next Monday at 9am' },
            { label: 'Schedule, no time', text: 'Publish it next Monday' },
            { label: 'Translate a page', text: 'Translate the About page into French' },
            { label: 'Edit a global', text: 'Update the copyright year in the footer' },
            { label: 'Edit a term', text: 'Rename the Community topic to Local community' },
            { label: 'Edit navigation', text: 'Add a Returns link to the header navigation' },
        ],
    },
    {
        title: 'Suggest a value',
        demos: [
            { label: 'Field suggestions', run: suggestSubtitle, text: 'Opens the summer sale and clicks the suggest icon on its subtitle' },
            // The same, but the AI doesn't respond. Like the AI down demos below, one can open Settings → AI and one can't.
            { label: 'Suggest down, can edit', run: suggestSubtitleFails, text: 'The same, but the AI doesn’t respond, and you can edit the AI settings', canEditAi: true },
            { label: 'Suggest down, can’t edit', run: suggestSubtitleFails, text: 'The same, but the AI doesn’t respond, and you can’t edit the AI settings', canEditAi: false },
            { label: 'Suggest empty', run: suggestSubtitleEmpty, text: 'The same, but the AI has no suggestions' },
            { label: 'Suggest slow', run: suggestSubtitleSlow, text: 'The same, but the AI takes a long time' },
            { label: 'Bard suggestions', run: suggestBody, text: 'Clicks the suggest icon on the summer sale’s Body, a Bard field, whose suggestions open over the sidebar' },
        ],
    },
    {
        title: 'Images',
        demos: [
            { label: 'Edit with AI', run: openAskAi, text: 'Opens an elephant photo in the asset editor and clicks Edit with AI: the card, with its skill pills' },
            { label: 'Make variations', run: () => runImageSkill('Make variations'), text: 'Runs Make variations, then previews each new image in the original’s place' },
            { label: 'Remove background', run: () => runImageSkill('Remove background'), text: 'Cuts out the elephants, on see-through and on white' },
            { label: 'Extend', run: () => runImageSkill('Extend'), text: 'Widens or heightens the image to Wide, Square and Portrait' },
            { label: 'Upscale', run: upscale, text: 'Makes it 2× and 4×, each previewed before and after, split down the middle' },
            { label: 'Erase something', run: eraseSomething, text: 'Picks Erase something from the list (+ 2), paints over part of the image, then erases it' },
            { label: 'Create a skill', run: createImageSkill, text: 'Types /moody-black-and-white, which matches nothing, creates it from the offer, saves it, then runs it', canEditAi: true },
            { label: 'Write alt text', run: writeAltText, text: 'Clicks the AI button on the Alt Text field: a pulsing line, then the text, with Added by AI and Undo' },
            { label: 'Suggest a filename', run: suggestFilename, text: 'Clicks Rename, then the AI button on its Filename field' },
        ],
    },
    {
        title: 'Skills',
        demos: [
            // Types a command that matches nothing, takes up the offer to create it, fills in the instructions and saves, then runs it.
            // The save is only pretend (see AiChatNewPrompt.vue), so nothing is left in Settings → AI and it can be played again.
            {
                label: 'Create a skill',
                text: '/price-check',
                create: { instructions: 'Check every price on the page against the current price list, and flag any that don’t match.' },
                canEditAi: true,
            },
        ],
    },
    {
        title: 'When it goes wrong',
        demos: [
            // The AI doesn't answer. Whether you can open Settings → AI decides what the outcome offers (see the "No response" scenario in mock-runs.js).
            { label: 'Nothing found', text: 'Which entries mention garden gnomes?' },
            { label: 'AI down, can edit AI', text: 'Summarise our returns policy', canEditAi: true },
            { label: 'AI down, can’t edit', text: 'Summarise our returns policy', canEditAi: false },
            { label: 'Chat slow', text: 'Summarise our shipping policy' },
        ],
    },
];

const phaseTwoDemos = [
    // Spoken rather than typed. It brings in the mic (and ⌘⇧D), which then stay until a phase 1 demo is played.
    { label: 'Dictate a request', text: 'Find all the entries that mention outdoor furniture', dictate: true, phase: 2 },
    // A scheduled prompt (see Settings → AI), run there and then rather than waiting for Monday. Not marked phase 2, as that brings in the mic,
    // which belongs to the dictation demo alone.
    { label: 'Content health report', text: 'Run the weekly content health report' },
    // Where its runs are kept, like a form's submissions (see AiReportsController)
    { label: 'Past reports', run: () => router.visit(cp_url('ai-reports/weekly-content-health-report')), text: 'Opens the weekly content health report’s past runs, in Tools → AI Reports' },
];

// Each group is a section of its own in the nav, with Phase 2 last
const sections = [...groups, { title: 'Coming later', demos: phaseTwoDemos }];
</script>

<template>
    <!-- At the very bottom of the nav (mt-auto), under everything else. Styled like the nav's own sections and links, with the same 24px between sections. -->
    <div class="mt-auto flex flex-col gap-6">
        <div v-for="section in sections" :key="section.title">
            <div class="section-title">{{ __(section.title) }}</div>
            <ul>
                <li v-for="item in section.demos" :key="item.label">
                    <a href="#" role="button" :title="item.text" @click.prevent="play($event, item)">
                        <span class="min-w-0 truncate">{{ __(item.label) }}</span>
                        <!-- 6px, in the AI colour, at the end of the link -->
                        <template v-if="lastPlayed === item.label">
                            <span class="ms-auto size-1.5 shrink-0 rounded-full bg-ai" aria-hidden="true" />
                            <span class="sr-only">{{ __('Last run') }}</span>
                        </template>
                    </a>
                </li>
            </ul>
        </div>

        <!-- The very last thing in the nav: whether clicking a demo jumps the nav back to the top. A small switch at the end of the link shows which way it is. -->
        <ul>
            <li>
                <a href="#" role="switch" :aria-checked="scrollToTop" @click.prevent="toggleScrollToTop">
                    <span class="min-w-0 truncate">{{ __('Scroll to top after a demo') }}</span>
                    <span :class="['relative ms-auto h-3.5 w-6 shrink-0 rounded-full transition-colors', scrollToTop ? 'bg-ai' : 'bg-gray-300 dark:bg-gray-600']" aria-hidden="true">
                        <span :class="['absolute top-0.5 size-2.5 rounded-full bg-white shadow-sm transition-[inset-inline-start]', scrollToTop ? 'start-3' : 'start-0.5']" />
                    </span>
                </a>
            </li>
        </ul>
    </div>
</template>
