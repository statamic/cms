<script setup>
import { nextTick, ref, useTemplateRef } from 'vue';
import { Button, Icon, Textarea } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { messages } = useAiChat();
const prompt = ref('');
const thread = useTemplateRef('thread');

// Prototype: same examples as the command palette.
const suggestions = [
    { text: 'Create a new homepage image', icon: 'assets' },
    { text: 'Create a blog post about our latest product', icon: 'entry' },
    { text: 'Create a landing page for the summer sale', icon: 'page' },
];

function send(text = prompt.value) {
    text = text.trim();
    if (!text) return;

    messages.value.push({ role: 'user', text });
    prompt.value = '';
    scrollToBottom();

    // Prototype: fake a reply.
    setTimeout(() => {
        messages.value.push({ role: 'assistant', text: __("This is a prototype, so I can't do that yet.") });
        scrollToBottom();
    }, 600);
}

function scrollToBottom() {
    nextTick(() => thread.value?.scrollTo({ top: thread.value.scrollHeight, behavior: 'smooth' }));
}
</script>

<template>
    <!-- Bleed the glow into the right panel's ps-2 padding -->
    <div class="ai-chat-glow flex flex-1 flex-col" style="--glow-bleed: calc(var(--spacing) * 2)">
        <div ref="thread" class="flex flex-1 flex-col overflow-y-auto ps-0.5 pe-2.5 py-2">
            <!-- mt-auto keeps messages next to the input, while still scrolling once they overflow -->
            <div class="relative mt-auto space-y-3">
                <div v-if="!messages.length" class="space-y-2">
                    <p class="px-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Try asking') }}</p>
                    <button
                        v-for="suggestion in suggestions"
                        :key="suggestion.text"
                        type="button"
                        class="group flex w-full cursor-pointer items-start gap-2.5 rounded-xl border border-gray-200 bg-gray-50 p-3 text-start text-sm/5 text-gray-700 shadow-ui-sm transition-colors hover:border-gray-300 hover:bg-white focus-visible:focus-outline dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-900"
                        @click="send(suggestion.text)"
                    >
                        <Icon :name="suggestion.icon" class="mt-0.5 size-4 shrink-0 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" />
                        <span class="flex-1">{{ suggestion.text }}</span>
                        <Icon name="arrow-right" class="mt-0.5 size-3.5 shrink-0 text-gray-400 opacity-0 transition-opacity group-hover:opacity-100 rtl:rotate-180" />
                    </button>
                </div>
                <div
                    v-for="(message, i) in messages"
                    :key="i"
                    :class="[
                        'max-w-[85%] rounded-xl px-3 py-2 text-sm',
                        message.role === 'user'
                            ? 'ms-auto bg-gray-900 text-white dark:bg-gray-200 dark:text-gray-900'
                            : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                    ]"
                    v-text="message.text"
                />
            </div>
        </div>

        <form class="relative p-2.5 ps-0.25" @submit.prevent="send()" @keydown.enter.exact.prevent="send()">
            <Textarea v-model="prompt" :placeholder="__('Ask anything...')" rows="2" resize="none" elastic />
            <Button type="submit" icon="arrow-up" variant="primary" size="xs" round class="absolute end-5 bottom-5" :aria-label="__('Send')" :disabled="!prompt.trim()" />
        </form>
    </div>
</template>
