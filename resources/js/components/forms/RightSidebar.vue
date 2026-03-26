<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <div class="rounded-lg border border-gray-300/80 dark:border-gray-700 bg-white dark:bg-gray-925 p-3">
        <h2 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2" v-text="title" />
        <ul class="flex flex-col gap-1.5">
            <li v-for="(link, i) in links" :key="i">
                <Link
                    :href="link.url"
                    class="flex items-center gap-2 rounded-md px-2 py-1 text-sm text-gray-700 hover:bg-gray-400/15 dark:text-gray-300"
                >
                    <span v-text="link.text" />
                </Link>
            </li>
        </ul>
    </div>
</template>
