<script setup>
import { computed, nextTick, onMounted, ref, useTemplateRef, watch } from 'vue';
import { Button, Icon, Textarea } from '@ui';
import AiChatExamples from './AiChatExamples.vue';
import AiChatRun from './AiChatRun.vue';
import { planRun, playRun } from './mock-runs.js';
import useAiChat from '@/composables/use-ai-chat.js';

const { open, messages, floating, focusRequests, leave, newChat } = useAiChat();
// Floating, the input sits straight on the card with no box of its own (the card carries the focus edge). Docked, it keeps its usual white box, which gets the same focused decoration as the floating card: the purple-to-blue gradient edge, a 1px blue line just inside it (2px in all, like a focused field) and a purple glow.
const inputClasses = computed(() =>
    floating.value
        ? // The middle of the lowercase letters sits about 1.4px below the middle of the capitals, so even padding looks low unless we nudge it.
        'border-0 pt-[3.5px]! pb-[6px]! placeholder:text-gray-500 shadow-none! outline-hidden ps-1! pe-12! dark:bg-transparent!'
        : 'pe-10 focus:border-transparent focus:[background:linear-gradient(white,white)_padding-box,linear-gradient(135deg,var(--color-primary),#60a5fa)_border-box] dark:focus:[background:linear-gradient(var(--color-gray-900),var(--color-gray-900))_padding-box,linear-gradient(135deg,var(--color-primary),#60a5fa)_border-box] focus:outline-1 focus:-outline-offset-2 focus:outline-blue-400 focus:shadow-[0_10px_40px_-10px_color-mix(in_oklab,var(--color-primary)_45%,transparent)]',
);
const prompt = ref('');

// Floating, the examples are hidden until you press / in the empty box (a hint shows while it's focused and empty). Docked, they show above the input.
const showExamples = ref(false);
const thread = useTemplateRef('thread');
const form = useTemplateRef('form');
const examples = useTemplateRef('examples');
// Floating, the examples are only there once opened. Docked, they're there until the first message.
const examplesVisible = computed(() => !messages.value.length && (floating.value ? showExamples.value : true));

// Move focus into the chat when it opens, so keyboard and screen reader users land on the input.
// Closing is done with the same header button, which still has focus, so there's nothing to restore.
const focusInput = () => nextTick(() => form.value?.querySelector('textarea')?.focus());
watch(open, (isOpen) => {
    if (isOpen) focusInput();
    else showExamples.value = false; // the floating chat stays mounted while closed, so put the examples away
});
// The keyboard shortcut asks for focus when the chat is already open.
watch(focusRequests, focusInput);
// Switching between docked and floating remounts the chat, so refocus then too.
onMounted(() => open.value && focusInput());

// Prototype: same examples as the command palette.
const suggestions = [
    { text: 'Create a new homepage image', icon: 'assets' },
    { text: 'Create a blog post about our latest product', icon: 'entry' },
    { text: 'Create a landing page for the summer sale', icon: 'page' },
];

watch(prompt, (value) => value && (showExamples.value = false));

// When focus moves to something outside the chat, put the examples away. (The key hint hides itself, as it only shows while focus is inside the card.)
function onFocusOut(event) {
    if (!event.currentTarget.contains(event.relatedTarget)) showExamples.value = false;
}

// Enter sends from the input. On a card it does what a button does, and picks it.
function onEnter(event) {
    if (event.target.tagName !== 'TEXTAREA') return;

    event.preventDefault();
    send();
}

function onKeydown(event) {
    // From the empty input, up and down go into the example cards.
    if (event.target.tagName === 'TEXTAREA' && (event.key === 'ArrowDown' || event.key === 'ArrowUp') && !prompt.value && examplesVisible.value && examples.value) {
        event.preventDefault();
        event.key === 'ArrowDown' ? examples.value.focusFirst() : examples.value.focusLast();
        return;
    }

    if (floating.value && event.key === '/' && !prompt.value && !event.metaKey && !event.ctrlKey && !event.altKey) {
        event.preventDefault();
        showExamples.value = true;
    } else if (event.key === 'Escape') {
        // Stop other Escape handlers in the CP (like closing a stack) from also acting.
        event.stopPropagation();

        if (showExamples.value) {
            showExamples.value = false;
            focusInput();
        } else {
            // Leave the chat open, and hand focus back to the page.
            leave();
        }
    }
}

// The input grows with what you type (the autosize library sets its height), but it only notices typing.
// Clearing it from code leaves it as tall as the message was, so tell it to measure again.
function shrinkInput() {
    nextTick(() => form.value?.querySelector('textarea')?.dispatchEvent(new Event('autosize:update')));
}

function send(text = prompt.value) {
    text = text.trim();
    if (!text) return;

    showExamples.value = false;

    // Typing /clear (or just clear) starts a new chat. Floating, a leading / opens the examples, so the bare word works there.
    if (/^\/?(clear|new)$/i.test(text)) {
        prompt.value = '';
        shrinkInput();
        newChat();
        return;
    }

    const message = { role: 'user', text };
    messages.value.push(message);
    prompt.value = '';
    shrinkInput();
    focusInput();
    scrollToBottom();

    // Prototype: answer with the tool calls the MCP would make, played one after another.
    const run = planRun(text);
    setTimeout(() => {
        // The chat was cleared while we waited
        if (!messages.value.includes(message)) return;

        messages.value.push({ role: 'assistant', run });
        scrollToBottom();
        playRun(run, scrollToBottom);
    }, 500);
}

function scrollToBottom() {
    nextTick(() => thread.value?.scrollTo({ top: thread.value.scrollHeight, behavior: 'smooth' }));
}
</script>

<template>
    <!-- Docked in the sidebar, fill the height. Floating, be only as tall as the content. -->
    <div data-ai-chat :class="['relative flex min-h-0 flex-col', floating ? 'flex-initial' : 'flex-1']" @focusout="onFocusOut">
        <!-- Floating, there is nothing to show above the input until the first message. Docked, it fills the space. -->
        <!-- As the thread scrolls, messages fade out under the top edge (only once it has scrolled, via a scroll-driven animation, so nothing fades at rest) instead of being cut off by a hard line, and the top corners follow the card's -->
        <div v-if="messages.length || !floating" ref="thread" :class="['flex min-h-0 flex-col overflow-y-auto ps-0.5 pe-2.5 pt-3.5 pb-2 ai-chat-thread', floating ? 'flex-initial rounded-t-[11px]' : 'flex-1']">
            <!-- mt-auto keeps messages next to the input, while still scrolling once they overflow -->
            <div class="relative mt-auto space-y-3">
                <!-- Docked, show the examples above the input until a request has been sent -->
                <AiChatExamples v-if="!floating && !messages.length" ref="examples" :examples="suggestions" @pick="send" @exit="focusInput" />
                <template v-for="(message, i) in messages" :key="i">
                    <AiChatRun v-if="message.run" :run="message.run" />
                    <div
                        v-else
                        :class="[
                            'max-w-[85%] rounded-xl px-3 py-2 text-sm',
                            // A new turn gets a little more room above it, so it doesn't merge with the answer before
                            message.role === 'user' && i > 0 ? 'mt-7.5' : 'mb-5',
                            message.role === 'user'
                                // Your messages are a white card with a ring, like the CP's Card and the tool-call cards
                                ? 'ms-auto bg-white text-gray-900 ring ring-gray-200 dark:bg-gray-850 dark:text-gray-200 dark:ring-gray-700/80'
                                : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                        ]"
                        v-text="message.text"
                    />
                </template>
            </div>
        </div>
        <form
            ref="form"
            :class="[
                'relative',
                floating ? 'p-2' : 'p-2.5',
                // Like the command palette's search row, there's a divider above the input. It runs edge to edge, so floating it steps out into the card's left padding
                messages.length || !floating ? 'border-t border-gray-200/80 dark:border-gray-950' : '',
                floating && messages.length ? '-ms-2.25 ps-2.5' : 'ps-0.25',
            ]"
            @submit.prevent="send()"
            @keydown.enter.exact="onEnter"
            @keydown="onKeydown"
        >
            <!-- Examples, opened by pressing / in the empty box -->
            <!-- The same fade up as the chat itself, in and out, a touch quicker -->
            <Transition
                enter-active-class="transition duration-150 ease-out motion-reduce:transition-none"
                enter-from-class="translate-y-3 opacity-0"
                leave-active-class="transition duration-150 ease-out motion-reduce:transition-none"
                leave-to-class="translate-y-3 opacity-0"
            >
                <div
                    v-if="floating && showExamples && !messages.length"
                    class="absolute -start-2.75 bottom-full z-20 mb-3 rounded-2xl border shadow-none border-transparent [background:linear-gradient(white,white)_padding-box,linear-gradient(135deg,var(--color-primary),#60a5fa)_border-box] dark:[background:linear-gradient(var(--color-gray-900),var(--color-gray-900))_padding-box,linear-gradient(135deg,var(--color-primary),#60a5fa)_border-box]"
                >
                    <AiChatExamples ref="examples" :examples="suggestions" padded @pick="send" @exit="focusInput" />
                </div>
            </Transition>
            <!-- Like the command palette's search row, an icon in front of the text -->
            <Icon
                name="ai-chat"
                :class="['pointer-events-none absolute z-10 size-4 text-gray-400', floating ? (messages.length ? 'start-[19px]' : 'start-2.5') + ' top-[16.5px]' : 'start-3 top-[26px]']"
            />
            <Textarea
                v-model="prompt"
                :placeholder="floating ? __('Ask anything…') : __('Ask anything...')"
                rows="1"
                resize="none"
                elastic
                :class="[inputClasses, floating ? 'ps-8.5!' : 'ps-9.5!']"
            />
            <!-- Floating, a hint about the / key sits next to the send button while the chat is focused and nothing has been typed. Just a hint, not a button. Docked, the examples are already showing. -->
            <div
                v-if="floating && !messages.length"
                v-show="!prompt && !showExamples"
                aria-hidden="true"
                class="pointer-events-none absolute end-12.5 bottom-[12.5px] flex h-6.5 items-center gap-1.5 px-1.5 text-xs text-gray-500 opacity-0 transition-opacity group-focus-within/chat:opacity-100 dark:text-gray-400"
            >
                <kbd class="rounded-md bg-gray-950/5 px-[0.3125rem] py-[0.0625rem] font-sans text-[0.625rem]/4 font-medium ring-1 ring-gray-950/10 dark:bg-white/5 dark:ring-white/10">/</kbd>
                {{ __('examples') }}
            </div>
            <Button
                type="submit"
                icon="arrow-up"
                variant="primary"
                size="xs"
                round
                :class="floating ? 'end-4.75 bottom-[11.75px]' : 'end-5.25 bottom-5.25'"
                class="absolute border-0 bg-none! bg-primary! shadow-none! inset-shadow-none! transition-[background-color] duration-150 hover:bg-primary-hover! disabled:opacity-100! disabled:bg-primary/8! dark:disabled:bg-primary/20! [&_svg]:opacity-100! disabled:[&_svg]:text-primary/50! dark:disabled:[&_svg]:text-white/50! [&_g]:[stroke-width:1.5]"
                :aria-label="__('Send')"
                v-tooltip="__('Send')"
                :disabled="!prompt.trim()"
            />
        </form>
    </div>
</template>

<style>
@property --ai-chat-fade {
    syntax: '<length>';
    inherits: false;
    initial-value: 0px;
}

@keyframes ai-chat-thread-fade {
    to { --ai-chat-fade: 50px; }
}

/* The fade grows from nothing to 50px over the first 50px of scrolling */
.ai-chat-thread {
    mask-image: linear-gradient(to bottom, transparent, black var(--ai-chat-fade));
    animation: ai-chat-thread-fade linear both;
    animation-timeline: scroll(self);
    animation-range: 0 50px;
}
</style>
