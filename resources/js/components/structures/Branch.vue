<template>

    <div class="flex">
        <div class="page-move w-6" />

        <div class="flex items-center flex-1 p-1 ml-1 text-xs leading-normal">

            <div
                class="page-text"
                :class="{ 'text-sm font-medium': isRoot }"
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
    },

    computed: {

        isRoot() {
            return this.depth === 1;
        }

    }

}
</script>
