<script setup>
import { ref, useTemplateRef, watch, watchEffect } from 'vue';
import { Icon } from '@ui';
import { moving } from './mock-runs.js';

// While the chat takes you to another page, the whole page (nav and global header included) dims under a dark wash, and the middle of the
// header says where it's going, with the page loading bar along the top in the AI colours instead of the usual lime. Once it's there, they stay over the
// new page for a moment, then lift. The dimming comes in over half a second, a little longer than the pause before the page changes, so it's
// still deepening as the new page arrives and the two read as one movement. It lifts quickly, so the move never feels held up.
// The page's layer sits at the same level as the floating chat but comes before it, so the chat stays clear. The global header is above the
// whole page area, so it gets a layer of its own, sent to the end of the body so it can sit over the header.
const wash = 'bg-gray-950/30 dark:bg-black/50';

// Once it's there, the message stays as the dimming lifts, and turns into "You're now on…", so it's clear you've moved. It then fades
// after a few seconds. A new move takes over straight away.
const arrived = ref(null);
let arrivedTimer = null;

// The message stays where it is as it turns into "You're now on…": its left edge is held where the "Taking you to…" one was, so the icon
// doesn't move however long the new words are. (Centred, a shorter or longer message would shift it.)
const message = useTemplateRef('message');
const heldLeft = ref(null);
watch(
    () => moving.title,
    (title, was) => {
        clearTimeout(arrivedTimer);
        // (This runs before the page is redrawn, so the message is still "Taking you to…")
        heldLeft.value = !title && was ? message.value?.getBoundingClientRect().left ?? null : null;
        arrived.value = !title && was && Date.now() - (moving.arrivedAt ?? 0) < 3000 ? was : null;
        if (arrived.value) arrivedTimer = setTimeout(() => (arrived.value = null), 3500);
    },
);

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
            leave-active-class="transition duration-500 ease-out motion-reduce:transition-none"
            leave-to-class="opacity-0"
        >
            <div
                v-if="moving.title || arrived"
                :class="['pointer-events-none fixed inset-x-0 top-0 z-(--z-index-portal) flex h-14 items-center transition-colors duration-300 ease-out', moving.title ? wash : '', heldLeft === null ? 'justify-center' : '']"
            >
                <!-- The message covers whatever is in the middle of the header (like long breadcrumbs) with the header's own colour, dimmed like the
                     rest of it while the page is, and its sides fade out so it doesn't look like a box -->
                <p ref="message" role="status" :data-arrived="!moving.title || undefined" :style="heldLeft === null ? {} : { marginInlineStart: `${heldLeft}px` }" class="ai-move-message flex h-full items-center gap-2 px-10 text-sm text-white/75">
                    <span class="flex items-center gap-2">
                        <!-- A 20px spinner on the way. Once it's there, the spinner's arc closes into a ring of the same size, and a tick draws in, in the same
                             grey and line weight, so it reads as the same icon finishing. -->
                        <Icon v-if="moving.title" name="loading" class="size-5! shrink-0" />
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ai-ring-close size-5! shrink-0" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" pathLength="1" transform="rotate(-90 12 12)" />
                            <path d="m9 12.5 2 2 4-4.5" pathLength="1" class="ai-ring-tick" />
                        </svg>
                        <!-- Both openings sit in the same space, as wide as the longer one and lined up against the page's name, so the name doesn't shift
                             as one becomes the other -->
                        <span class="grid justify-items-end">
                            <span :class="['col-start-1 row-start-1', moving.title ? '' : 'invisible']">{{ __('Taking you to') }}</span>
                            <span :class="['col-start-1 row-start-1', moving.title ? 'invisible' : '']">{{ __('You’re now on') }}</span>
                        </span>
                        <span class="-ms-1 font-medium text-white">{{ moving.title ?? arrived }}<span v-if="moving.title" class="font-normal text-white/75">…</span></span>
                    </span>
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


/* The spinner's arc closes into a full ring, then the tick draws in. Reduced motion shows them straight away. */
@media (prefers-reduced-motion: no-preference) {
    .ai-ring-close circle {
        stroke-dasharray: 1;
        animation: ai-ring-close 350ms ease-out both;
    }
    .ai-ring-close .ai-ring-tick {
        stroke-dasharray: 1;
        animation: ai-tick-draw 350ms cubic-bezier(0.65, 0, 0.35, 1) 300ms both;
    }
}
@keyframes ai-ring-close {
    from {
        stroke-dashoffset: 0.25;
    }
}
@keyframes ai-tick-draw {
    from {
        stroke-dashoffset: 1;
    }
}

/* Once you're there and the page has lifted, it's the header's own colour */
.ai-move-message {
    transition: background 300ms ease-out;
}
.ai-move-message[data-arrived] {
    background: var(--color-global-header-bg);
}

.dark .ai-move-message:not([data-arrived]) {
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
