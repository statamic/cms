<script setup>
import { Button } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { messages, close, leave } = useAiChat();

// Put the chat away and hand focus back to where it was before the chat, like Esc does.
function dismiss() {
    close();
    leave();
}
</script>

<template>
    <!-- Closes the docked chat without the keyboard, once a chat has started: a small x in the thread's top right, shown on hover or focus. Before the first message there's no room for it, and Ask closes it. It waits half a second before fading once you leave the chat, so it doesn't vanish as you head for it. -->
    <Button
        v-if="messages.length"
        size="sm"
        icon="x"
        :aria-label="__('Close')"
        v-tooltip="__('Close')"
        class="absolute end-2 top-2 z-(--z-index-above) size-7! opacity-0 shadow-none! transition-opacity delay-500 focus-visible:opacity-100 group-hover/chat:delay-0 focus-visible:delay-0 group-hover/chat:opacity-100 pointer-coarse:opacity-100 [&_svg]:size-3.5!"
        @click="dismiss"
    />
</template>
