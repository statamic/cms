<script setup>
import { Button } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { messages, close, leave, floating } = useAiChat();

// Put the chat away and hand focus back to where it was before the chat, like Esc does.
function dismiss() {
    close();
    leave();
}
</script>

<template>
    <!-- Closes the chat without the keyboard, once a chat has started: a small x, shown on hover or focus. Floating, it straddles the card's top right corner, with the dock button's look (white, a thin grey border and a shadow), so it never covers your first message. Docked, it sits in the thread's top right, the same 28px square. Before the first message there's no room for it, and Ask closes it. -->
    <!-- Floating, it's only on desktop: under 1000px the Ask pill sits right under the card and closes it, and there's less room round the card for it. -->
    <!-- Half of it hangs outside the card, so an invisible patch 26px bigger all round sits under it, and hovering that counts as hovering the card. Coming at it from outside, it shows before you reach it. It's separate from the button, so a click near it doesn't close the chat. -->
    <!-- It also waits half a second before fading once you leave the card, so it doesn't vanish as you head for it from inside -->
    <span v-if="messages.length && floating" aria-hidden="true" class="absolute -end-10 -top-10 z-(--z-index-above) size-20 max-[999px]:hidden" />
    <Button
        v-if="messages.length"
        size="sm"
        icon="x"
        :aria-label="__('Close')"
        v-tooltip="__('Close')"
        :class="
            floating
                ? '-end-3.5 -top-3.5 rounded-lg! border! border-gray-200! bg-white! text-gray-600! shadow-md! hover:text-gray-900! max-[999px]:hidden! dark:border-gray-600! dark:bg-gray-800! dark:text-gray-300! dark:hover:text-white!'
                : 'end-2 top-2 shadow-none!'
        "
        class="absolute z-(--z-index-above) size-7! opacity-0 transition-opacity delay-500 focus-visible:opacity-100 group-hover/chat:delay-0 focus-visible:delay-0 group-hover/chat:opacity-100 pointer-coarse:opacity-100 [&_svg]:size-3.5!"
        @click="dismiss"
    />
</template>
