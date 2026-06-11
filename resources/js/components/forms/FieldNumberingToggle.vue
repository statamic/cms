<script setup>
import { ToggleGroup, ToggleItem } from '@ui';
import { useFieldNumberingPreference } from '@/composables/forms/field-numbering';

const { showFieldNumbers } = useFieldNumberingPreference();

const toggleFieldNumbers = () => {
    showFieldNumbers.value = ! showFieldNumbers.value;
};
</script>

<template>
    <ToggleGroup :model-value="showFieldNumbers ? 'on' : null" size="xs">
        <ToggleItem
            value="on"
            icon="mail-sign-hashtag"
            :aria-label="__('Show field numbers')"
            v-tooltip="__('Show field numbers')"
            @click.prevent="toggleFieldNumbers"
        />
    </ToggleGroup>
</template>
