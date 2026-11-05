<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import useAiChat from '@/composables/use-ai-chat.js';
import { cp_url } from '@/bootstrap/globals.js';
import { suggestionsEmpty, suggestionsFail, suggestionsSlow } from '@/components/ai-suggest/use-ai-suggest.js';
import { canEditAi } from './mock-runs.js';

// Prototype: buttons at the bottom of the nav that play out a request in the AI chat, for demoing the branch. Each starts a new chat and
// types the request in, as if you had. What happens is up to the mock runs (see mock-runs.js). Phase 2's are things for later, which
// only show in the chat while one of them is being demoed (see phaseTwo in use-ai-chat.js).
const { demo, close } = useAiChat();

// The demos are at the bottom of the nav, so once one is clicked, jump the nav back to the top, so it looks as it normally would.
// The demo starts a couple of seconds later, so you see the page settle first. Clicking another in the meantime starts that one instead.
let starting = null;

// The demo played last, marked with a purple dot in the nav so you can see where you are in the list
const lastPlayed = ref(null);

// Scrolling back up shows the nav's scrollbar, which flashes in and out. So it's hidden for the length of the scroll, and back to normal after.
function hideScrollbarWhileScrolling(nav) {
    const done = () => nav.classList.remove('ai-demos-scrolling');
    nav.classList.add('ai-demos-scrolling');
    nav.addEventListener('scrollend', done, { once: true });
    setTimeout(done, 1000);
    nav.scrollTo({ top: 0 });
    // Already at the top, so there's no scroll to wait for
    if (nav.scrollTop === 0) done();
}
function play(event, item) {
    const nav = event.currentTarget.closest('.nav-main');
    if (nav) hideScrollbarWhileScrolling(nav);
    lastPlayed.value = item.label;
    if (item.canEditAi !== undefined) canEditAi.value = item.canEditAi;
    clearTimeout(starting);
    starting = setTimeout(() => (item.run ? runOnPage(item.run) : demo(item.text, { dictate: item.dictate, phase: item.phase })), 2000);
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
async function suggestSubtitle({ wait, find }, fails = false, empty = false, slow = false) {
    if (!window.location.href.startsWith(summerSaleUrl)) router.visit(summerSaleUrl);
    // The field's id is on its input, so find that, then the suggest button beside it: the nearest parent that has one
    const input = await find('[id^="field_"][id$="_subtitle"]');
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

const demos = [
    { label: 'Update homepage', text: 'How can I update the homepage?' },
    { label: 'Fix summer sale', text: 'Tighten up the summer sale entry' },
    { label: 'Write a blog post', text: 'Create a blog post about our new garden range' },
    { label: 'Add links to a post', text: 'Add links throughout the garden range blog post' },
    { label: 'Find content', text: 'Which entries mention outdoor furniture?' },
    { label: 'Field suggestions', run: suggestSubtitle, text: 'Opens the summer sale and clicks the suggest icon on its subtitle' },
    // The AI doesn't answer. Whether you can open Settings → AI decides what the outcome offers (see the "No response" scenario in mock-runs.js).
    { label: 'Nothing found', text: 'Which entries mention garden gnomes?' },
    { label: 'AI down, can edit AI', text: 'Summarise our returns policy', canEditAi: true },
    { label: 'AI down, can’t edit', text: 'Summarise our returns policy', canEditAi: false },
    // The same, but the AI doesn't respond. Like the AI down demos above, one can open Settings → AI and one can't.
    { label: 'Suggest down, can edit', run: suggestSubtitleFails, text: 'The same, but the AI doesn’t respond, and you can edit the AI settings', canEditAi: true },
    { label: 'Suggest down, can’t edit', run: suggestSubtitleFails, text: 'The same, but the AI doesn’t respond, and you can’t edit the AI settings', canEditAi: false },
    { label: 'Suggest empty', run: suggestSubtitleEmpty, text: 'The same, but the AI has no suggestions' },
    { label: 'Suggest slow', run: suggestSubtitleSlow, text: 'The same, but the AI takes a long time' },
    { label: 'Chat slow', text: 'Summarise our shipping policy' },
    // The ideas from the research notes (see ideaScenarios in mock-runs.js)
    { label: 'Fix a typo', text: "Fix the typo 'recieve' on the Contact page" },
    { label: 'Schedule publishing', text: 'Publish it next Monday at 9am' },
    { label: 'Schedule, no time', text: 'Publish it next Monday' },
    { label: 'Find missing alt text', text: 'Which images have no alt text?' },
    { label: 'Translate a page', text: 'Translate the About page into French' },
    { label: 'Summarise submissions', text: "Summarise this week's contact form submissions" },
    { label: 'Edit a global', text: 'Update the copyright year in the footer' },
    { label: 'Add a field', text: 'Add a subtitle field to the blog blueprint' },
];

const phaseTwoDemos = [
    // Spoken rather than typed. It brings in the mic (and ⌘⇧D), which then stay until a phase 1 demo is played.
    { label: 'Dictate a request', text: 'Find all the entries that mention outdoor furniture', dictate: true, phase: 2 },
];

const sections = [
    { title: 'AI Demos', demos },
    { title: 'AI Demos / Phase 2', demos: phaseTwoDemos },
];
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
    </div>
</template>

<style>
.nav-main.ai-demos-scrolling {
    scrollbar-width: none;
    &::-webkit-scrollbar {
        display: none;
    }
}
</style>
