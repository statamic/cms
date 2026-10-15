<script setup>
import { computed, nextTick, onMounted, ref, useTemplateRef, watch } from 'vue';
import { Button, Textarea } from '@ui';
import AiChatExamples from './AiChatExamples.vue';
import useAiChat from '@/composables/use-ai-chat.js';

const { open, messages, floating } = useAiChat();
// Floating, the input sits straight on the card with no box of its own. Docked, it keeps its usual white box.
const inputClasses = computed(() =>
    floating.value
        ? // The middle of the lowercase letters sits about 1.4px below the middle of the capitals, so even padding looks low unless we nudge it.
        'border-0 pt-[3.5px]! pb-[6px]! placeholder:text-gray-500 shadow-none! outline-hidden ps-1! pe-10! dark:bg-transparent!'
        : 'pe-10',
);
const prompt = ref('');


// Floating, the examples are hidden until you press / in the empty box. Docked, they show above the input.
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
    if (floating.value && event.key === '/' && !prompt.value && !event.metaKey && !event.ctrlKey && !event.altKey) {
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
                <!-- Docked, show the examples above the input until a request has been sent -->
                <AiChatExamples v-if="!floating && !messages.length" :examples="suggestions" @pick="send" />
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

        <form ref="form" :class="['relative ps-0.25', floating ? 'p-2' : 'p-2.5']" @submit.prevent="send()" @keydown.enter.exact.prevent="send()" @keydown="onKeydown">
            <!-- Examples, opened by pressing / in the empty box -->
            <div
                v-if="floating && showExamples && !messages.length"
                class="absolute start-0 end-2.5 bottom-full z-20 rounded-2xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-700 dark:bg-gray-900"
            >
                <AiChatExamples :examples="suggestions" @pick="send" />
            </div>
            <Textarea
                v-model="prompt"
                :placeholder="floating ? __('Ask anything, or press / for examples') : __('Ask anything...')"
                rows="1"
                resize="none"
                elastic
                :class="inputClasses"
            />
            <Button
                type="submit"
                icon="arrow-up"
                variant="primary"
                size="xs"
                round
                :class="floating ? 'end-3.25 bottom-3.25' : 'end-5.25 bottom-5.25'"
                class="absolute border-0 bg-none! bg-primary! shadow-none! inset-shadow-none! transition-[background-color] duration-150 hover:bg-primary-hover! disabled:opacity-100! disabled:bg-primary/8! dark:disabled:bg-primary/20! [&_svg]:opacity-100! disabled:[&_svg]:text-primary/50! dark:disabled:[&_svg]:text-white/50! [&_g]:[stroke-width:1.5]"
                :aria-label="__('Send')"
                v-tooltip="__('Send')"
                :disabled="!prompt.trim()"
            />
        </form>
    </div>
</template>
