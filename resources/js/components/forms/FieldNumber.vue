<script setup>
import { computed, inject } from 'vue';

const props = defineProps({
    fieldKey: { type: String, default: null },
});

const fieldNumbers = inject('fieldNumbers', null);

const number = computed(() => fieldNumbers?.value?.get(props.fieldKey) ?? null);
</script>

<template>
    <span
        v-if="number"
        data-field-number
        class="font-mono text-2xs text-gray-500 tabular-nums dark:text-gray-400"
    >{{ number }}.</span>
</template>
