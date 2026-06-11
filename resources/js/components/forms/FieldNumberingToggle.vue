<script setup>
import { Button } from '@ui';
import { useFieldNumberingPreference } from '@/composables/forms/field-numbering';

const { showFieldNumbers } = useFieldNumberingPreference();
</script>

<template>
    <Button
        size="xs"
        icon="list-ol"
        :variant="showFieldNumbers ? 'filled' : 'subtle'"
        :aria-label="__('Show field numbers')"
        :aria-pressed="showFieldNumbers"
        v-tooltip="__('Show field numbers')"
        @click="showFieldNumbers = !showFieldNumbers"
    />
</template>
