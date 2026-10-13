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
        class="inline-flex items-center justify-center shrink-0 cursor-pointer h-8 w-8 rounded-lg hover:bg-white/15 aria-pressed:bg-white/15 [&_svg]:size-4 [&_svg]:text-white/85"
        @click="toggle"
    >
        <Icon name="ai-chat" />
    </button>
</template>
