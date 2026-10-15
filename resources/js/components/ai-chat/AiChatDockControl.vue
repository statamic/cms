<script setup>
import { computed } from 'vue';
import { Icon } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { floating, focusRequests } = useAiChat();

// This is the last thing in the chat, so Tab from it loops back to the input rather than leaving the card.
function loopToInput(event) {
    if (event.shiftKey) return;

    event.preventDefault();
    focusRequests.value++;
}

const label = computed(() => (floating.value ? __('Dock to sidebar') : __('Float in corner')));
const icon = computed(() => (floating.value ? 'bar-sidebar-left-panel-open' : 'bar-sidebar-right-panel-open'));
</script>

<template>
    <!-- Switches between the floating card and the sidebar. Floating, it straddles the card's corner so it never covers the input. -->
    <button
        type="button"
        :aria-label="label"
        v-tooltip="label"
        :class="[
            // Same size and shape in both layouts, so the focus outline matches
            'absolute z-10 flex size-7 cursor-pointer items-center justify-center rounded-lg text-gray-600 transition-opacity hover:text-gray-900 focus-visible:opacity-100 focus-visible:focus-outline group-hover/chat:opacity-100 dark:text-gray-300 dark:hover:text-white',
            floating
                // Anchored from the bottom, level with where the card's top corner is when it's just the input bar, so it stays put as messages make the card grow upwards
                ? '-end-3.5 bottom-[37.5px] border border-gray-200 bg-white opacity-0 shadow-md dark:border-gray-600 dark:bg-gray-800'
                : 'end-2 top-2 opacity-60 hover:bg-gray-950/5 dark:hover:bg-white/10',
        ]"
        @click="floating = !floating"
        @keydown.tab="loopToInput"
    >
        <Icon :name="icon" class="size-3.5" />
    </button>
</template>
