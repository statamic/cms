<template>
    <span
        v-if="moving"
        class="pointer-events-none absolute start-full top-3 ms-2 flex items-center gap-0.5"
        aria-hidden="true"
    >
        <kbd :class="keyClasses">↑</kbd>
        <kbd :class="keyClasses">↓</kbd>
    </span>
</template>

<script>
export default {
    props: {
        moving: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        keyClasses() {
            return 'inline-flex h-4 min-w-4 items-center justify-center rounded border border-blue-200 bg-blue-50 px-1 text-[0.625rem] font-semibold text-blue-600 dark:border-blue-700 dark:bg-gray-800 dark:text-blue-300';
        },
    },
};
</script>
