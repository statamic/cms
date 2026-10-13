<script setup>
import { Icon } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { open, toggle } = useAiChat();
</script>

<template>
    <button
        type="button"
        :aria-label="__('Ask')"
        :aria-pressed="open"
        v-tooltip="__('Ask')"
        class="inline-flex items-center justify-center shrink-0 cursor-pointer h-8 w-8 rounded-xl ring-1 ring-transparent hover:bg-white/15 aria-pressed:bg-black/40 aria-pressed:ring-white/10 [&_svg]:size-4 [&_svg]:text-white/85"
        @click="toggle"
    >
        <Icon name="ai-chat" />
    </button>
</template>
