<template>
    <div class="breadcrumb flex">
        <a :href="url" class="flex-initial flex p-2 -m-2 items-center text-xs text-gray-700 hover:text-gray-900">
            <svg-icon name="micro/chevron-right" class="h-6 w-4 rotate-180" />
            <span v-text="__(title)" />
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
