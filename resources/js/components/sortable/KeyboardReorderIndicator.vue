<template>
    <span
        v-if="moving"
        class="pointer-events-none absolute top-3 flex items-center gap-0.5"
        :class="side === 'start' ? 'end-full me-2' : 'start-full ms-2'"
        aria-hidden="true"
    >
        <!-- In a tree, ← and → only show when they'd do something: ← when it can move out a level, → when it can nest under the page above. -->
        <kbd v-if="horizontal && can.out" class="inline-flex h-4 min-w-4 items-center justify-center rounded border border-blue-300 bg-blue-50 px-1 text-[0.625rem] font-semibold text-blue-600 dark:border-blue-500 dark:bg-blue-950 dark:text-blue-300">←</kbd>
        <kbd class="inline-flex h-4 min-w-4 items-center justify-center rounded border border-blue-300 bg-blue-50 px-1 text-[0.625rem] font-semibold text-blue-600 dark:border-blue-500 dark:bg-blue-950 dark:text-blue-300">↑</kbd>
        <kbd class="inline-flex h-4 min-w-4 items-center justify-center rounded border border-blue-300 bg-blue-50 px-1 text-[0.625rem] font-semibold text-blue-600 dark:border-blue-500 dark:bg-blue-950 dark:text-blue-300">↓</kbd>
        <kbd v-if="horizontal && can.in" class="inline-flex h-4 min-w-4 items-center justify-center rounded border border-blue-300 bg-blue-50 px-1 text-[0.625rem] font-semibold text-blue-600 dark:border-blue-500 dark:bg-blue-950 dark:text-blue-300">→</kbd>
    </span>
</template>

<script>
export default {
    props: {
        /** Show ← → as well (trees), when `can` says they'd do something */
        horizontal: { type: Boolean, default: false },
        /** Which of ← → would do something right now: `in` nests under the item above, `out` moves out a level */
        can: { type: Object, default: () => ({ in: false, out: false }) },
        moving: {
            type: Boolean,
            default: false,
        },
        /** `start` = outside the left (handle) side; `end` = outside the right (menu) side */
        side: {
            type: String,
            default: 'end',
            validator: (value) => ['start', 'end'].includes(value),
        },
    },
};
</script>
