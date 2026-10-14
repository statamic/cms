<script setup>
import { nextTick, onMounted, ref, useTemplateRef, watch } from 'vue';
import { Button, Icon, Textarea } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { open, messages, floating } = useAiChat();
const prompt = ref('');
// Examples are hidden until you press / in the empty box.
const showExamples = ref(false);
const thread = useTemplateRef('thread');
const form = useTemplateRef('form');

// Move focus into the chat when it opens, so keyboard and screen reader users land on the input.
// Closing is done with the same header button, which still has focus, so there's nothing to restore.
const focusInput = () => nextTick(() => form.value?.querySelector('textarea')?.focus());
watch(open, (isOpen) => isOpen && focusInput());
// Switching between docked and floating remounts the chat, so refocus then too.
onMounted(() => open.value && focusInput());

// Prototype: same examples as the command palette.
const suggestions = [
    { text: 'Create a new homepage image', icon: 'assets' },
    { text: 'Create a blog post about our latest product', icon: 'entry' },
    { text: 'Create a landing page for the summer sale', icon: 'page' },
];

watch(prompt, (value) => value && (showExamples.value = false));

function onKeydown(event) {
    if (event.key === '/' && !prompt.value && !event.metaKey && !event.ctrlKey && !event.altKey) {
        event.preventDefault();
        showExamples.value = true;
    } else if (event.key === 'Escape' && showExamples.value) {
        event.stopPropagation();
        showExamples.value = false;
        focusInput();
    }
}

function send(text = prompt.value) {
    text = text.trim();
    if (!text) return;

    showExamples.value = false;

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
    <!-- Docked in the sidebar, fill the height. Floating, be only as tall as the content. -->
    <div :class="['relative flex min-h-0 flex-col', floating ? 'flex-initial' : 'flex-1']">
        <!-- Floating, there is nothing to show above the input until the first message. Docked, it fills the space. -->
        <div v-if="messages.length || !floating" ref="thread" :class="['flex min-h-0 flex-col overflow-y-auto ps-0.5 pe-2.5 py-2', floating ? 'flex-initial' : 'flex-1']">
            <!-- mt-auto keeps messages next to the input, while still scrolling once they overflow -->
            <div class="relative mt-auto space-y-3">
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

        <form ref="form" class="relative p-2 ps-0.25" @submit.prevent="send()" @keydown.enter.exact.prevent="send()" @keydown="onKeydown">
            <!-- Examples, opened by pressing / in the empty box -->
            <div
                v-if="showExamples && !messages.length"
                class="absolute start-0 end-2.5 bottom-full z-20 space-y-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-700 dark:bg-gray-900"
            >
                <p class="px-1 pt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Try asking') }}</p>
                <button
                    v-for="suggestion in suggestions"
                    :key="suggestion.text"
                    type="button"
                    class="group flex w-full cursor-pointer items-start gap-2.5 rounded-xl border border-gray-200 bg-gray-50 p-3 text-start text-sm/5 text-gray-700 shadow-ui-sm transition-colors hover:border-gray-300 hover:bg-white focus-visible:focus-outline dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-gray-600 dark:hover:bg-gray-900"
                    @click="send(suggestion.text)"
                >
                    <Icon :name="suggestion.icon" class="mt-0.5 size-4 shrink-0 text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-200" />
                    <span class="flex-1">{{ suggestion.text }}</span>
                    <Icon name="arrow-right" class="mt-0.5 size-3.5 shrink-0 text-gray-600 dark:text-gray-400 opacity-0 transition-opacity group-hover:opacity-100 rtl:rotate-180" />
                </button>
            </div>
            <Textarea v-model="prompt" :placeholder="__('Ask anything, or press / for examples')" rows="1" resize="none" elastic class="border-0! bg-transparent! py-2! shadow-none! outline-hidden! ps-1! pe-10! dark:bg-transparent!" />
            <Button
                type="submit"
                icon="arrow-up"
                variant="primary"
                size="xs"
                round
                class="absolute end-3.75 bottom-3.75 bg-none! bg-primary! disabled:opacity-100! hover:bg-primary-hover! border-0 shadow-none! inset-shadow-none! [&_svg]:opacity-100! [&_g]:[stroke-width:1.5]"
                :aria-label="__('Send')"
                v-tooltip="__('Send')"
                :disabled="!prompt.trim()"
            />
        </form>
    </div>
</template>
