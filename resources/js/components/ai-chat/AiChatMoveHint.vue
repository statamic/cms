<script setup>
import { watchEffect } from 'vue';
import { Icon } from '@ui';
import { moving } from './mock-runs.js';

// While the chat takes you to another page, the whole page (nav and global header included) dims under a dark wash, and the middle of the
// header says where it's going, with the page loading bar along the top in the AI colours instead of the usual lime. Once it's there, they stay over the
// new page for a moment, then lift. The dimming comes in over half a second, a little longer than the pause before the page changes, so it's
// still deepening as the new page arrives and the two read as one movement. It lifts quickly, so the move never feels held up.
// The page's layer sits at the same level as the floating chat but comes before it, so the chat stays clear. The global header is above the
// whole page area, so it gets a layer of its own, sent to the end of the body so it can sit over the header.
const wash = 'bg-gray-950/30 dark:bg-black/50';

// The chat's own loading bar stands in for the usual one while it moves you, so the lime one is hidden (see the styles below)
watchEffect(() => document.documentElement.toggleAttribute('data-ai-moving', !!moving.title));
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
            <div v-if="moving.title" :class="['pointer-events-none fixed inset-x-0 top-0 z-(--z-index-portal) flex h-14 items-center justify-center', wash]">
                <!-- The message covers whatever is in the middle of the header (like long breadcrumbs) with the header's own colour, dimmed like the
                     rest of it, and its sides fade out so it doesn't look like a box -->
                <p role="status" class="ai-move-message flex h-full items-center gap-2 px-10 text-sm text-white/75">
                    <Icon name="loading" class="size-3.5" />
                    <span>{{ __('Taking you to') }} <span class="font-medium text-white">{{ moving.title }}</span>…</span>
                </p>
            </div>
        </Transition>

        <!-- The page loading bar (see progress.css), in the AI colours: it creeps most of the way across while the chat moves you, then fills and fades as it lets go -->
        <Transition leave-active-class="ai-move-bar-done">
            <div v-if="moving.title" class="ai-move-bar pointer-events-none fixed start-0 top-0 z-(--z-index-portal) h-0.5" />
        </Transition>
    </Teleport>
</template>

<style>
.ai-move-message {
    background: linear-gradient(rgb(from var(--color-gray-950) r g b / 0.3), rgb(from var(--color-gray-950) r g b / 0.3)), var(--color-global-header-bg);
    mask-image: linear-gradient(90deg, transparent, black 2rem, black calc(100% - 2rem), transparent);
}

.dark .ai-move-message {
    background: linear-gradient(rgb(0 0 0 / 0.5), rgb(0 0 0 / 0.5)), var(--color-global-header-bg);
}

/* Like nprogress: it creeps towards the end while it waits, then fills the rest and fades out. Reduced motion skips the creep. */
.ai-move-bar {
    width: 85%;
    background: linear-gradient(90deg, var(--color-ai), var(--color-ai-end));
    animation: ai-move-bar 1.2s cubic-bezier(0.1, 0.6, 0.3, 1) both;
}

.ai-move-bar-done {
    animation: ai-move-bar-done 400ms ease-out both;
}

@keyframes ai-move-bar {
    from {
        width: 0;
    }
}

@keyframes ai-move-bar-done {
    60% {
        width: 100%;
        opacity: 1;
    }
    to {
        width: 100%;
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .ai-move-bar {
        animation: none;
    }
}

[data-ai-moving] #nprogress {
    display: none;
}
</style>
