<script setup>
import { nextTick, ref, useTemplateRef } from 'vue';
import { Button, Heading, Textarea } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { messages, close } = useAiChat();
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
    <div class="flex h-full flex-col">
        <header class="flex items-center justify-between gap-2 px-3 py-2">
            <Heading size="lg" :text="__('Ask')" />
            <Button icon="x" variant="ghost" size="sm" :aria-label="__('Close')" @click="close" />
        </header>

        <div ref="thread" class="flex-1 space-y-3 overflow-y-auto ps-2 pe-3 py-2">
            <div v-if="!messages.length" class="space-y-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Try asking') }}</p>
                <button
                    v-for="suggestion in suggestions"
                    :key="suggestion"
                    type="button"
                    class="block w-full cursor-pointer rounded-lg border border-gray-200 px-3 py-2 text-start text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
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

        <form class="relative p-3" @submit.prevent="send()" @keydown.enter.exact.prevent="send()">
            <Textarea v-model="prompt" :placeholder="__('Ask anything...')" rows="2" resize="none" elastic />
            <Button type="submit" icon="arrow-up" variant="primary" size="xs" round class="absolute end-5 bottom-5" :aria-label="__('Send')" :disabled="!prompt.trim()" />
        </form>
    </div>
</template>
