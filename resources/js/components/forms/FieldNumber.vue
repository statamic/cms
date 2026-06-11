<script setup>
defineProps({
    number: { type: Number, default: null },
});
</script>

<template>
    <span
        v-if="number"
        data-field-number
        class="font-mono text-2xs text-gray-500 tabular-nums dark:text-gray-400"
    >{{ number }}.</span>
</template>
