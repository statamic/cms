<template>

    <div class="flex">
        <div v-if="!hasChildren" class="w-full rounded p-2 border border-gray-500 border-dashed text-xs text-gray-600 text-center">
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
        }
    }

}
</script>
