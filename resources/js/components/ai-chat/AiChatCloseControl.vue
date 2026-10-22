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
    <!-- Closes the chat without the keyboard, once a chat has started. It's where the Clear button used to be, and looks like it did, but in the neutral grey of a default button, with the x after the word. It sits over the thread's top right corner in both layouts, the same distance from the top and the side, and shows on hover or focus. Before the first message there's no room for it, and the header's Ask button closes it. -->
    <!-- "Close" is mostly lowercase, so it looks low next to the x. Nudge the word up 1px so they look level. -->
    <Button
        v-if="messages.length"
        size="sm"
        icon-append="x"
        :text="__('Close')"
        class="absolute end-2 top-2 z-(--z-index-above) opacity-0 shadow-none! transition-opacity focus-visible:opacity-100 group-hover/chat:opacity-100 pointer-coarse:opacity-100 [&_svg]:size-3.5! [&>div]:-translate-y-px"
        @click="dismiss"
    />
</template>
