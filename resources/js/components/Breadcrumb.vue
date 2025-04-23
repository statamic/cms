<template>
    <div class="breadcrumb flex">
        <a
            :href="url"
            class="-m-2 flex flex-initial items-center p-2 text-xs text-gray-700 hover:text-gray-900 dark:text-dark-175 dark:hover:text-dark-100"
        >
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
    },
};
</script>
