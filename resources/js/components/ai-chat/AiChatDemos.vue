<script setup>
import { ref } from 'vue';
import useAiChat from '@/composables/use-ai-chat.js';

// Prototype: buttons at the bottom of the nav that play out a request in the AI chat, for demoing the branch. Each starts a new chat and
// types the request in, as if you had. What happens is up to the mock runs (see mock-runs.js). Phase 2's are things for later, which
// only show in the chat while one of them is being demoed (see phaseTwo in use-ai-chat.js).
const { demo } = useAiChat();

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
    clearTimeout(starting);
    starting = setTimeout(() => demo(item.text, { dictate: item.dictate, phase: item.phase }), 2000);
}

const demos = [
    { label: 'Update homepage', text: 'How can I update the homepage?' },
    { label: 'Fix summer sale', text: 'Tighten up the summer sale entry' },
    { label: 'Write a blog post', text: 'Create a blog post about our new garden range' },
    { label: 'Add links to a post', text: 'Add links throughout the garden range blog post' },
    { label: 'Find content', text: 'Which entries mention outdoor furniture?' },
    // The ideas from the research notes (see ideaScenarios in mock-runs.js)
    { label: 'Fix a typo', text: "Fix the typo 'recieve' on the Contact page" },
    { label: 'Draft a post', text: 'Write a blog post about our new opening hours' },
    { label: 'Schedule publishing', text: 'Publish it next Monday at 9am' },
    { label: 'Schedule, no time', text: 'Publish it next Monday' },
    { label: 'Find missing alt text', text: 'Which images have no alt text?' },
    { label: 'Translate a page', text: 'Translate the About page into French' },
    { label: 'Summarise submissions', text: "Summarise this week's contact form submissions" },
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
