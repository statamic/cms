<script setup>
import { ref, useTemplateRef, watch, watchEffect } from 'vue';
import { Icon } from '@ui';
import { moving, writing } from './mock-runs.js';

// While the chat takes you to another page, the whole page (nav and global header included) dims under a dark wash, and the middle of the
// header says where it's going, with the page loading bar along the top in the AI colours instead of the usual lime. Once it's there, they stay over the
// new page for a moment, then lift. The dimming comes in over half a second, a little longer than the pause before the page changes, so it's
// still deepening as the new page arrives and the two read as one movement. It lifts quickly, so the move never feels held up.
// The page's layer sits at the same level as the floating chat but comes before it, so the chat stays clear. The global header is above the
// whole page area, so it gets a layer of its own, sent to the end of the body so it can sit over the header.
const wash = 'bg-gray-950/30';

// Once it's there, the message stays as the dimming lifts, and turns into "You're now on…", so it's clear you've moved. It then fades
// after a few seconds. A new move takes over straight away.
const arrived = ref(null);
let arrivedTimer = null;

// The message stays where it is as it turns into "You're now on…": its starting edge is held where the "Taking you to…" one was, so the icon
// doesn't move however long the new words are. (Centred, a shorter or longer message would shift it.)
const message = useTemplateRef('message');
const heldStart = ref(null);
// How far the message's starting edge is from that side of the screen: the left, or the right in a right-to-left language
function startOf(element) {
    if (!element) return null;
    const { left, right } = element.getBoundingClientRect();
    return document.documentElement.dir === 'rtl' ? window.innerWidth - right : left;
}
watch(
    () => moving.title,
    (title, was) => {
        clearTimeout(arrivedTimer);
        // (This runs before the page is redrawn, so the message is still "Taking you to…")
        heldStart.value = !title && was ? startOf(message.value) : null;
        arrived.value = !title && was && Date.now() - (moving.arrivedAt ?? 0) < 3000 ? was : null;
        if (arrived.value) arrivedTimer = setTimeout(() => (arrived.value = null), 3500);
    },
);

// While the chat fills in the page's fields (and the page is locked), the same place in the header says so, in place of "You're now on…".
// Once it's done, it finishes the way a move does: the spinner closes into a ring with a tick, the words say it's done, holding still,
// and it fades after a few seconds.
const wrote = ref(null);
let wroteTimer = null;
watch(
    () => writing.active,
    (active) => {
        clearTimeout(arrivedTimer);
        clearTimeout(wroteTimer);
        arrived.value = null;
        // Whatever's showing ("You're now on…" as it starts, "Writing…" as it ends) becomes the new words in place, the way "Taking you to…"
        // becomes "You're now on…": its starting edge is held where it was, so the icon doesn't move. With nothing showing, the message comes in
        // centred. (This runs before the page is redrawn, so the old message is still there to measure.)
        heldStart.value = startOf(message.value);
        wrote.value = active ? null : pageTitle();
        if (wrote.value) wroteTimer = setTimeout(() => (wrote.value = null), 3500);
    },
);
// A move takes over from it straight away
watch(
    () => moving.title,
    (title) => title && (clearTimeout(wroteTimer), (wrote.value = null)),
);

const pageTitle = () => document.title.split(/ [‹›] /)[0];

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
                v-if="moving.title || arrived || writing.active || wrote"
                :class="['pointer-events-none fixed inset-x-0 top-0 z-(--z-index-portal) flex h-14 items-center transition-colors duration-300 ease-out', moving.title ? wash : '', heldStart === null ? 'justify-center' : '']"
            >
                <!-- The message covers whatever is in the middle of the header (like long breadcrumbs) with the header's own colour, dimmed like the
                     rest of it while the page is, and its sides fade out so it doesn't look like a box -->
                <p ref="message" role="status" :data-arrived="!moving.title || undefined" :style="heldStart === null ? {} : { marginInlineStart: `${heldStart}px` }" class="ai-move-message flex h-full items-center gap-2 px-10 text-sm text-white/75">
                    <!-- Writing in the page: the same spinner, and why the page is locked. Then, once it's done, the same ring and tick as arriving. -->
                    <span v-if="(writing.active || wrote) && !moving.title" class="flex items-center gap-2">
                        <Icon v-if="writing.active" name="loading" class="size-5! shrink-0" />
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ai-ring-close size-5! shrink-0" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" pathLength="1" transform="rotate(-90 12 12)" />
                            <path d="m9 12.5 2 2 4-4.5" pathLength="1" class="ai-ring-tick" />
                        </svg>
                        <!-- Both sets of words sit in the same space, as wide as the longer, so nothing shifts as one becomes the other. They're about as
                             long as "You're now on…", so the message doesn't jump in size as one turns into the next. -->
                        <span class="grid">
                            <span :class="['col-start-1 row-start-1', writing.active ? '' : 'invisible']">{{ __('Writing your changes…') }}</span>
                            <span :class="['col-start-1 row-start-1', writing.active ? 'invisible' : '']">{{ __('Done, ready to check') }}</span>
                        </span>
                    </span>
                    <span v-else class="flex items-center gap-2">
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
