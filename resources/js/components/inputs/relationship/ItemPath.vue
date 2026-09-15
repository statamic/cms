<template>
    <span v-if="path.length" class="shrink-[999] truncate text-gray-500 dark:text-gray-400" :title="fullPath">
        <template v-for="(segment, i) in segments" :key="i">
            {{ segment }}
            <ui-icon
                name="chevron-right"
                class="inline size-3 align-[-0.15em] text-gray-300 dark:text-gray-600"
                aria-hidden="true"
            />
        </template>
    </span>
</template>

<script>
export default {
    props: {
        path: { type: Array, default: () => [] },
    },

    computed: {
        translatedPath() {
            return this.path.map((segment) => __(segment));
        },

        fullPath() {
            return this.translatedPath.join(' › ');
        },

        segments() {
            if (this.translatedPath.length <= 4) return this.translatedPath;

            return [this.translatedPath[0], '…', ...this.translatedPath.slice(-2)];
        },
    },
};
</script>
