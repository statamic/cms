<script setup>
import { Icon } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

// Prototype: buttons at the bottom of the nav that play out a request in the AI chat, for demoing the branch. Each starts a new chat and
// types the request in, as if you had. What happens is up to the mock runs (see mock-runs.js).
const { demo } = useAiChat();

const demos = [
    { label: 'Update homepage', icon: 'ai-chat', text: 'How can I update the homepage?' },
    { label: 'Fix summer sale', icon: 'ai-edit', text: 'Tighten up the summer sale entry' },
    { label: 'Write a blog post', icon: 'ai-sparks', text: 'Create a blog post about our new garden range' },
    { label: 'Find content', icon: 'ai-search-spark', text: 'Which entries mention outdoor furniture?' },
];
</script>

<template>
    <!-- At the very bottom of the nav (mt-auto), under everything else. Styled like the nav's own links. -->
    <div class="mt-auto">
        <div class="section-title">{{ __('AI Demos') }}</div>
        <ul>
            <li v-for="item in demos" :key="item.label">
                <a href="#" role="button" :title="item.text" @click.prevent="demo(item.text)">
                    <Icon :name="item.icon" />
                    <span>{{ __(item.label) }}</span>
                </a>
            </li>
        </ul>
    </div>
</template>
