<script setup>
import { Icon } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { floating } = useAiChat();
</script>

<template>
    <!-- Prototype: a slim handle along the right edge that switches between a floating card and the sidebar -->
    <button
        type="button"
        :aria-label="floating ? __('Dock to sidebar') : __('Float in corner')"
        v-tooltip="floating ? __('Dock to sidebar') : __('Float in corner')"
        class="group relative m-1.5 ms-0 flex w-5 shrink-0 cursor-pointer items-center justify-center self-stretch rounded-xl transition-colors hover:bg-gray-950/5 focus-visible:focus-outline dark:hover:bg-white/10"
        @click="floating = !floating"
    >
        <!-- A quiet grip at rest, swapped for the icon on hover or focus -->
        <span class="h-8 w-0.5 rounded-full bg-gray-300 transition-opacity group-hover:opacity-0 group-focus-visible:opacity-0 dark:bg-gray-600" />
        <Icon
            :name="floating ? 'bar-sidebar-right-panel-open' : 'fullscreen-open'"
            class="absolute size-3.5 text-gray-600 opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100 dark:text-gray-300"
        />
    </button>
</template>
