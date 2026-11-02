<script setup>
import { Icon } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

// Prototype: buttons at the bottom of the nav that play out a request in the AI chat, for demoing the branch. Each starts a new chat and
// types the request in, as if you had. What happens is up to the mock runs (see mock-runs.js).
const { demo } = useAiChat();

// The demos are at the bottom of the nav, so once one is clicked, jump the nav back to the top, so it looks as it normally would.
// The demo starts a couple of seconds later, so you see the page settle first. Clicking another in the meantime starts that one instead.
let starting = null;
function play(event, text) {
    event.currentTarget.closest('.nav-main')?.scrollTo({ top: 0 });
    clearTimeout(starting);
    starting = setTimeout(() => demo(text), 2000);
}

const demos = [
    { label: 'Update homepage', icon: 'ai-chat', text: 'How can I update the homepage?' },
    { label: 'Fix summer sale', icon: 'ai-edit', text: 'Tighten up the summer sale entry' },
    { label: 'Write a blog post', icon: 'ai-sparks', text: 'Create a blog post about our new garden range' },
    { label: 'Add links to a post', icon: 'link', text: 'Add links throughout the garden range blog post' },
    { label: 'Find content', icon: 'ai-search-spark', text: 'Which entries mention outdoor furniture?' },
];
</script>

<template>
    <!-- At the very bottom of the nav (mt-auto), under everything else. Styled like the nav's own links. -->
    <div class="mt-auto">
        <div class="section-title">{{ __('AI Demos') }}</div>
        <ul>
            <li v-for="item in demos" :key="item.label">
                <a href="#" role="button" :title="item.text" @click.prevent="play($event, item.text)">
                    <Icon :name="item.icon" />
                    <span>{{ __(item.label) }}</span>
                </a>
            </li>
        </ul>
    </div>
</template>
