<script setup>
import { nextTick, ref, useTemplateRef } from 'vue';
import { Button, Textarea } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { messages } = useAiChat();
const prompt = ref('');
const thread = useTemplateRef('thread');

// Prototype: same examples as the command palette.
const suggestions = [
    'Create a new homepage image',
    'Create a blog post about our latest product',
    'Create a landing page for the summer sale',
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
        <div ref="thread" class="flex flex-1 flex-col overflow-y-auto ps-1.5 pe-3 py-2">
            <!-- mt-auto keeps messages next to the input, while still scrolling once they overflow -->
            <div class="relative mt-auto space-y-3">
                <div v-if="!messages.length" class="space-y-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Try asking') }}</p>
                    <button
                        v-for="suggestion in suggestions"
                        :key="suggestion"
                        type="button"
                        class="block w-full cursor-pointer rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-start text-sm text-gray-700 hover:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900"
                        @click="send(suggestion)"
                        v-text="suggestion"
                    />
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

        <form class="relative p-3 ps-1.5" @submit.prevent="send()" @keydown.enter.exact.prevent="send()">
            <Textarea v-model="prompt" :placeholder="__('Ask anything...')" rows="2" resize="none" elastic />
            <Button type="submit" icon="arrow-up" variant="primary" size="xs" round class="absolute end-5 bottom-5" :aria-label="__('Send')" :disabled="!prompt.trim()" />
        </form>
    </div>
</template>
