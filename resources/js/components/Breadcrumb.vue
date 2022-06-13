<template>
    <div class="breadcrumb flex">
        <a :href="url" class="flex-initial flex p-1 -m-1 items-center text-xs text-grey-70 hover:text-grey-90">
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
