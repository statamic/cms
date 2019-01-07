<template>

    <div class="flex">
        <div class="page-move w-6" v-if="!root" />

        <div v-if="root" class="page-root">
            <i class="icon icon-home mx-auto opacity-25"></i>
        </div>

        <div class="flex items-center flex-1 p-1 ml-1 text-xs leading-normal">

            <div
                class="page-text"
                :class="{ 'text-sm font-medium': isTopLevel }"
            >
                <a :href="page.edit_url">{{ page.title }}</a>
            </div>

        </div>
    </div>

</template>

<script>
export default {

    props: {
        page: Object,
        depth: Number,
        root: Boolean
    },

    computed: {

        isTopLevel() {
            return this.depth === 1;
        }

    }

}
</script>
