<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

// Marks, on the line through the ticks of the chat's step cards, the page the cards below it happened on: a dot, with the page's name in line with the card titles.
// The line sits 21.5px in, so its 1px width is centred on the ticks (the cards' 14px padding plus half a 16px tick).
// The page you're on has a filled AI-coloured dot with a soft ring round it, and says so. Any other is a link back to it.
// Each page after the first has more room above it (24px rather than the cards' 12px; margins between siblings overlap, so mt-6 is the whole gap), so each page's cards read as a group.
const props = defineProps({
    page: { type: Object, required: true },
    /** The page the cards above were on, if any, so the line can carry on from them up to this dot */
    previous: { type: Object, default: null },
});

const inertiaPage = usePage();
const isHere = (p) => p?.url === inertiaPage.url.split('?')[0];
const here = computed(() => isHere(props.page));

// The same line as between the cards (see AiChatRun.vue): a soft AI colour beside the page you're on, dashed grey beside pages you've left
const line = (p) => (isHere(p) ? 'w-px bg-ai/30' : 'border-s border-dashed border-gray-300 dark:border-gray-600');
</script>

<template>
    <div :class="['relative flex items-center gap-2 ps-10 text-xs', previous ? 'mt-6!' : '']">
        <!-- The line joins up through the dot: from the cards above, and on down to the cards below -->
        <span v-if="previous" :class="['absolute start-[21.5px] -top-6 bottom-1/2', line(previous)]" />
        <span :class="['absolute start-[21.5px] top-1/2 bottom-0', line(page)]" />
        <span :class="['absolute start-[17px] size-2.5 rounded-full ring-4 ring-white dark:ring-gray-900', here ? 'bg-ai outline-4 outline-ai/20' : 'border border-gray-400 bg-white dark:border-gray-500 dark:bg-gray-900']" />
        <span v-if="here" class="text-gray-900 dark:text-white">{{ page.title }} <span class="text-gray-500 dark:text-gray-400">· {{ __("You're here") }}</span></span>
        <button v-else type="button" class="cursor-pointer text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white" @click="router.visit(page.url)">{{ page.title }}</button>
    </div>
</template>
