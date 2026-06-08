<script setup>
defineProps({
    pages: { type: Array, required: true },
    fields: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});
</script>

<template>
    <ul class="space-y-2">
        <li
            v-for="field in fields"
            :key="field._id"
            class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-white/10 dark:bg-gray-900 dark:text-gray-100"
        >
            {{ field.display }}
        </li>
    </ul>
</template>
