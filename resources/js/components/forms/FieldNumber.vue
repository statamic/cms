<script setup lang="ts">
import { computed, inject, type ComputedRef } from 'vue';

const props = defineProps<{
    fieldKey?: string | null;
}>();

const fieldNumbers = inject<ComputedRef<Map<string, number>> | null>('fieldNumbers', null);

const number = computed(() => (props.fieldKey ? fieldNumbers?.value?.get(props.fieldKey) ?? null : null));
</script>

<template>
    <span
        v-if="number"
        data-field-number
        class="font-mono text-2xs text-gray-500 tabular-nums dark:text-gray-400"
    >{{ number }}.</span>
</template>
