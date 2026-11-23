<template>
    <span
        v-if="moving"
        class="pointer-events-none absolute flex items-center gap-0.5"
        :class="{
            'top-3 end-full me-2': side === 'start',
            'top-3 start-full ms-2': side === 'end',
            'top-1/2 end-12 -translate-y-1/2': side === 'inside',
        }"
        aria-hidden="true"
    >
        <kbd
            v-for="key in keys"
            :key="key"
            class="inline-flex h-4 min-w-4 items-center justify-center rounded border border-blue-300 bg-blue-50 px-1 text-[0.625rem] font-semibold text-blue-600 dark:border-blue-500 dark:bg-blue-950 dark:text-blue-300"
        >{{ key }}</kbd>
    </span>
</template>

<script>
export default {
    props: {
        moving: {
            type: Boolean,
            default: false,
        },
        /** The arrow keys that move it: up and down for a list, and left and right too in a tree */
        keys: {
            type: Array,
            default: () => ['↑', '↓'],
        },
        /**
         * `start` = outside the left (handle) side; `end` = outside the right (menu) side; `inside` = inside the item, just before its
         * menu, for where the outside would be clipped (e.g. a tree, in a panel)
         */
        side: {
            type: String,
            default: 'end',
            validator: (value) => ['start', 'end', 'inside'].includes(value),
        },
    },
};
</script>
