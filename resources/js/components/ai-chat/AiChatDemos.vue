<script setup>
import { Icon } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

// Prototype: buttons at the bottom of the nav that play out a request in the AI chat, for demoing the branch. Each starts a new chat and
// types the request in, as if you had. What happens is up to the mock runs (see mock-runs.js). Phase 2's are things for later, which
// only show in the chat while one of them is being demoed (see phaseTwo in use-ai-chat.js).
const { demo } = useAiChat();

// The demos are at the bottom of the nav, so once one is clicked, jump the nav back to the top, so it looks as it normally would.
// The demo starts a couple of seconds later, so you see the page settle first. Clicking another in the meantime starts that one instead.
let starting = null;
function play(event, item) {
    event.currentTarget.closest('.nav-main')?.scrollTo({ top: 0 });
    clearTimeout(starting);
    starting = setTimeout(() => demo(item.text, { dictate: item.dictate, phase: item.phase }), 2000);
}

const demos = [
    { label: 'Update homepage', icon: 'ai-chat', text: 'How can I update the homepage?' },
    { label: 'Fix summer sale', icon: 'ai-edit', text: 'Tighten up the summer sale entry' },
    { label: 'Write a blog post', icon: 'ai-sparks', text: 'Create a blog post about our new garden range' },
    { label: 'Add links to a post', icon: 'link', text: 'Add links throughout the garden range blog post' },
    { label: 'Find content', icon: 'ai-search-spark', text: 'Which entries mention outdoor furniture?' },
    // The ideas from the research notes (see ideaScenarios in mock-runs.js)
    { label: 'Fix a typo', icon: 'pencil-line', text: "Fix the typo 'recieve' on the Contact page" },
    { label: 'Draft a post', icon: 'entry', text: 'Write a blog post about our new opening hours' },
    { label: 'Schedule publishing', icon: 'calendar', text: 'Publish it next Monday at 9am' },
    { label: 'Schedule, no time given', icon: 'calendar-date', text: 'Publish it next Monday' },
    { label: 'Find missing alt text', icon: 'assets', text: 'Which images have no alt text?' },
    { label: 'Translate a page', icon: 'globe-world-wide-web', text: 'Translate the About page into French' },
    { label: 'Summarise submissions', icon: 'forms', text: "Summarise this week's contact form submissions" },
    { label: 'Add a field', icon: 'blueprint-edit', text: 'Add a subtitle field to the blog blueprint' },
];

const phaseTwoDemos = [
    // Spoken rather than typed. It brings in the mic (and ⌘⇧D), which then stay until a phase 1 demo is played.
    { label: 'Dictate a request', icon: 'computer-voice-mail-microphone', text: 'Find all the entries that mention outdoor furniture', dictate: true, phase: 2 },
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
                        <Icon :name="item.icon" />
                        <span>{{ __(item.label) }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
