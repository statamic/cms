<template>
    <div class="flex">
        <div
            v-if="!hasChildren"
            class="w-full rounded-sm border border-dashed border-gray-500 p-2 text-center text-xs text-gray-600"
        >
            &nbsp;
        </div>
    </div>
</template>

<script>
export default {
    props: {
        stat: Object,
    },

    computed: {
        hasChildren() {
            return this.stat.children.length > 0;
        },
    },
};
</script>
