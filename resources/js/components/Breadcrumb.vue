<template>
    <div class="breadcrumb flex">
        <a :href="url" class="flex-initial flex p-2 -m-2 items-center text-xs text-grey-700 hover:text-grey-900">
            <svg-icon name="chevron-right" class="h-6 w-4 rotate-180" />
            <span v-text="title" />
        </a>
    </div>
</template>

<script>
export default {

    props: {
        url: String,
        title: String,
    }

}
</script>
