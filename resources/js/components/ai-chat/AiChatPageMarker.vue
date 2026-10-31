<script setup>
import { computed, onMounted, ref, useTemplateRef } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Icon } from '@ui';

// Marks, on the line through the ticks of the chat's step cards, the page the cards below it happened on: a dot, with the page's name in line with the card titles.
// The line sits 21.5px in, so its 1px width is centred on the ticks (the cards' 14px padding plus half a 16px tick).
// The page you're on has a filled AI-coloured dot with a soft ring round it, which is enough to mark it. Any other is a link back to it.
// The purple dot's soft ring takes up some of the room before the page's icon, so a grey dot (with no ring) has its icon 4px further left
// to look the same distance away.
// Each page after the first has more room above it (24px rather than the cards' 12px; margins between siblings overlap, so mt-6 is the whole gap), so each page's cards read as a group.
const props = defineProps({
    page: { type: Object, required: true },
    /** The page the cards above were on, if any, so the line can carry on from them up to this dot */
    previous: { type: Object, default: null },
    /** Whether the page has only just come up in a run that's playing, so the dot comes down into place (rather than every time the chat is drawn again) */
    arriving: { type: Boolean, default: false },
    /** Whether this is the latest page in a run that's still going. Its dot stays purple after the chat has moved you on, until the next page's dot comes to take over. */
    latest: { type: Boolean, default: false },
});

const inertiaPage = usePage();
const isHere = (p) => p?.url === inertiaPage.url.split('?')[0];
const here = computed(() => isHere(props.page));
// The dot is purple on the page you're on. While a run moves you, that's still the last page's dot until the new page comes up in the trail
// and its dot sets off from there, so the purple never goes missing in between.
const purple = computed(() => here.value || props.latest);

// Each page shows an icon: an entry gets the entry icon, and anything else the same icon as its section in the CP's nav (Dashboard,
// Navigation, Assets…), noted when the chat was there (see mock-runs.js). The page icon if neither.
const icon = computed(() => (props.page.url.includes('/entries/') ? 'entry' : (props.page.icon ?? 'page')));

// The same line as between the cards (see AiChatRun.vue): a soft AI colour beside the page you're on, dashed grey beside pages you've left
const line = (p) => (isHere(p) ? 'w-px bg-ai/30' : 'border-s border-dashed border-gray-300 dark:border-gray-600');

// As a new page comes up, its dot sets off from the last page's dot and travels down the line to it, drawing a purple line behind it that
// then fades. So measure how far it's come from.
// Whether it's arriving is only known as the marker first appears (it's worked out from the time), so hold on to it, or the journey would be
// cut short when the chat next redraws.
const arriving = props.arriving;
const marker = useTemplateRef('marker');
const distance = ref(24);
// How far down the last page's marker is. From the markers' own positions, which the dot's movement doesn't affect. Markers are the same
// height, so it's the same distance between their dots.
function measure() {
    const markers = [...(marker.value?.parentElement?.querySelectorAll(':scope > [data-trail-marker]') ?? [])];
    const before = markers[markers.indexOf(marker.value) - 1];
    // (Nothing to measure if the chat is hidden as it's drawn, so it keeps the short drop then)
    const gap = before ? marker.value.offsetTop - before.offsetTop : 0;
    if (gap > 0) distance.value = gap;
}
// The cards above can still grow after this page comes up (the last one adds its result as it finishes), which moves the last page's dot.
// So keep measuring until the dot has set off, so it waits right on that dot and sets off from it.
onMounted(() => {
    if (!arriving || !props.previous) return;
    measure();
    const observer = new ResizeObserver(measure);
    observer.observe(marker.value.parentElement);
    setTimeout(() => observer.disconnect(), 1300);
});
const travels = computed(() => arriving && !!props.previous && distance.value > 24);
// A journey sets off from the last page's dot as soon as the new page has opened (see openEntry in mock-runs.js), quickly, under the cards,
// then settles gently into place, over 1.2s. The line it drew then fades over 0.8s. The first page's dot is just there, as the run starts
// on it.
const travelStyle = computed(() => ({ '--trail-from': `${-distance.value}px` }));
</script>

<template>
    <div ref="marker" data-trail-marker :class="['relative flex items-center gap-2 text-xs', purple ? 'ps-10' : 'ps-9', previous ? 'mt-6!' : '']" :style="travelStyle">
        <!-- The line joins up through the dot: from the cards above, and on down to the cards below. It sits under the travelling dot. -->
        <span v-if="previous" :class="['absolute -z-20 start-[21.5px] -top-6 bottom-1/2', line(previous)]" />
        <span :class="['absolute -z-20 start-[21.5px] top-1/2 bottom-0', line(page)]" />
        <!-- The purple line drawn down behind the dot as it travels (under the cards, like the dot), which then fades. It starts at the edge of the last page's dot rather than its middle, so it doesn't cross it. -->
        <span v-if="travels" class="ai-trail-line absolute -z-10 start-[21px] top-1/2 w-0.5 rounded-full bg-ai" :style="{ height: `${distance - 5}px`, marginTop: `${5 - distance}px` }" />
        <!-- The dot comes down the line into place as the page arrives, passing under the cards on the way, like the line, so it's only seen in the gaps between them.
             Layers, bottom up: the line (-z-20), the dots resting on it (-z-15), the travelling dot and the line it draws (-z-10), then the cards. So the travelling dot waits on top of the
             last page's dot, and never crosses over a card. -->
        <span
            data-trail-dot
            :class="[travels ? 'ai-trail-drop -z-10' : '-z-15', 'absolute start-[17px] size-2.5 rounded-full ring-4 ring-white dark:ring-gray-900', purple ? 'bg-ai outline-4 outline-ai/20' : 'border border-gray-400 bg-white dark:border-gray-500 dark:bg-gray-900']"
        />
        <!-- Every page's name is the same quiet grey, so the purple dot alone marks where you are. The pages you've left are links back. -->
        <span v-if="here" class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
            <Icon :name="icon" class="size-3.5! text-gray-400 dark:text-gray-500" />
            {{ page.title }}
        </span>
        <button v-else type="button" class="flex cursor-pointer items-center gap-1.5 text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white" @click="router.visit(page.url)">
            <!-- size-3.5! as Icon sets its own size-4 -->
            <Icon :name="icon" class="size-3.5! text-gray-400 dark:text-gray-500" />
            {{ page.title }}
        </button>
    </div>
</template>

<style>
/* The dot comes down the line from above, each on its own layer so it moves smoothly. Reduced motion skips it. */
@media (prefers-reduced-motion: no-preference) {
    .ai-trail-drop {
        will-change: transform;
        animation: ai-trail-drop 1200ms cubic-bezier(0.22, 1, 0.36, 1) both;
    }

    .ai-trail-line {
        transform-origin: top;
        will-change: transform, opacity;
        animation: ai-trail-line 1200ms cubic-bezier(0.22, 1, 0.36, 1) both, ai-trail-line-fade 800ms ease-out 1200ms both;
    }
}

/* Without motion, there's no line to draw */
@media (prefers-reduced-motion: reduce) {
    .ai-trail-line {
        display: none;
    }
}

@keyframes ai-trail-drop {
    from {
        transform: translateY(var(--trail-from));
    }
}

@keyframes ai-trail-line {
    from {
        transform: scaleY(0);
    }
}

@keyframes ai-trail-line-fade {
    to {
        opacity: 0;
    }
}
</style>
