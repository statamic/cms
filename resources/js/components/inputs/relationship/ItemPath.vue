<template>
    <template v-for="(segment, i) in path" :key="i">
        <ui-badge size="sm" :text="__(segment)" />
        <ui-icon
            name="chevron-right"
            class="size-3 shrink-0 text-gray-300 dark:text-gray-600"
            aria-hidden="true"
        />
    </template>
</template>

<script>
export default {
    props: {
        path: { type: Array, default: () => [] },
    },
};
</script>
