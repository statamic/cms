<script setup>
import { Button, ButtonGroup, Icon } from '@ui';
import useAiChat from '@/composables/use-ai-chat.js';

const { messages, floating, newChat } = useAiChat();

</script>

<template>
    <template v-if="messages.length">
        <!-- Clears the thread and starts a new chat. Floating it's the danger button as the listing's bulk actions toolbar shows it (white fading to red, red text), which it only gets inside a button group. It sits in the thread's top right corner and shows on hover. -->
        <ButtonGroup
            v-if="floating"
            class="absolute end-3 top-2 z-10 opacity-0 transition-opacity focus-within:opacity-100 group-hover/chat:opacity-100"
        >
            <Button variant="danger" size="sm" icon="trash" :text="__('Clear')" class="border-red-200! shadow-none! hover:to-red-100! hover:bg-red-50! dark:border-red-900/60! dark:hover:to-red-900/20! dark:hover:bg-gray-850! [&_svg]:size-3.5! [&_svg]:text-red-600! dark:[&_svg]:text-red-500!" @click="newChat" />
        </ButtonGroup>

        <!-- Docked it sits beside the dock control, as quiet red text. -->
        <button
            v-else
            type="button"
            class="absolute end-10 top-2 z-10 flex h-7 cursor-pointer items-center justify-center gap-1 rounded-lg px-2 text-xs text-red-600 opacity-0 transition-opacity hover:bg-red-600/5 focus-visible:opacity-100 focus-visible:focus-outline group-hover/chat:opacity-100 dark:text-red-400 dark:hover:bg-red-400/10"
            @click="newChat"
           
        >
            <Icon name="trash" class="size-3.5" />
            {{ __('Clear') }}
        </button>
    </template>
</template>
