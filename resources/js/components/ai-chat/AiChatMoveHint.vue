<script setup>
import { moving } from './mock-runs.js';

// While the chat takes you to another page, the whole page (nav and global header included) dims under a dark wash. Once it's there, the
// dimming stays over the new page for a moment, then lifts. The step cards in the chat show where it went (see AiChatPageMarker.vue), so
// there's no message here. The dimming comes in over half a second, a little longer than the pause before the page changes, so it's still
// deepening as the new page arrives and the two read as one movement. It lifts quickly, so the move never feels held up.
// The page's layer sits at the same level as the floating chat but comes before it, so the chat stays clear. The global header is above the
// whole page area, so it gets a layer of its own, sent to the end of the body so it can sit over the header.
const wash = 'bg-gray-950/30 dark:bg-black/50';
</script>

<template>
    <Transition
        enter-active-class="transition duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] motion-reduce:transition-none"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-300 ease-out motion-reduce:transition-none"
        leave-to-class="opacity-0"
    >
        <div v-if="moving.title" :class="['pointer-events-none fixed inset-0 z-(--z-index-modal)', wash]" />
    </Transition>

    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] motion-reduce:transition-none"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-300 ease-out motion-reduce:transition-none"
            leave-to-class="opacity-0"
        >
            <div v-if="moving.title" :class="['pointer-events-none fixed inset-x-0 top-0 z-(--z-index-portal) h-14', wash]" />
        </Transition>
    </Teleport>
</template>
