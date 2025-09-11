<script setup>
import { Select } from '@/components/ui';

defineProps({
    sites: { type: Array, required: true },
    value: { type: String, required: true },
});
</script>

<template>
    <Select
        :options="sites"
        option-label="name"
        option-value="handle"
        :model-value="value"
        @update:model-value="$emit('input', $event)"
    />
</template>
