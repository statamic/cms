<template>
    <div class="flex first-branch">
        <div
            v-if="!hasChildren"
            class="w-full rounded-lg border border-dashed border-blue-400 bg-blue-500/10 p-2"
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
