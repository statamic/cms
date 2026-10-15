<script setup>
import { onMounted, onUnmounted } from 'vue';
import { Icon } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { open, toggle, shortcut } = useAiChat();

const isMac = typeof navigator !== 'undefined' && /Mac|iPhone|iPad/.test(navigator.platform);
const modifierLabel = isMac ? '⌘' : 'Ctrl';

let binding = null;

onMounted(() => {
    binding = Statamic.$keys.bindGlobal(['mod+/'], (e) => {
        // Leave it to code editors, where the same shortcut comments a line.
        if (e.target?.closest?.('.cm-editor, .CodeMirror')) return;
        // And don't open the chat behind a modal.
        if (document.querySelector('[role="dialog"][data-state="open"]')) return;

        e.preventDefault();
        shortcut();
    });
});

onUnmounted(() => binding?.destroy());
</script>

<template>
    <button
        type="button"
        :aria-label="__('Ask')"
        :aria-pressed="open"
        :aria-keyshortcuts="'Control+/ Meta+/'"
        class="group inline-flex items-center justify-center gap-x-1.5 shrink-0 cursor-pointer h-8 min-w-8 sm:px-1.5 whitespace-nowrap rounded-xl text-xs text-white/60 hover:text-white/70 ring-1 ring-transparent hover:bg-white/15 aria-pressed:bg-black/40 aria-pressed:ring-white/10"
        @click="toggle"
    >
        <!-- Sized and coloured like the icon in the Ask or Search box -->
        <Icon name="ai-chat" class="size-5 p-[2.5px] flex-none text-white/50 group-hover:text-white/70" />
        <span class="sr-only leading-none sm:not-sr-only st-text-trim-cap">{{ __('Ask') }}</span>
        <!-- The same shortcut hint as the Ask or Search box -->
        <kbd class="ml-auto md:ml-1.5 hidden self-center rounded-md bg-white/5 px-[0.3125rem] py-[0.0625rem] text-[0.625rem]/4 font-medium text-white/60 group-hover:text-white/70 ring-1 ring-white/7.5 [word-spacing:-0.15em] ring-inset md:block">
            <kbd class="font-sans">{{ modifierLabel }} </kbd><kbd class="font-sans">/</kbd>
        </kbd>
    </button>
</template>
