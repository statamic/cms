<script setup>
defineProps({
    pages: { type: Array, required: true },
    fields: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});
</script>

<template>
    <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600 dark:text-gray-400">
        {{ __('Tree view prototype') }}
    </div>
</template>
