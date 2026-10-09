<script setup lang="ts">
import { computed } from 'vue';
import { ToggleGroup, ToggleItem } from '@ui';
import { useFieldNumberingPreference } from '@/composables/forms/field-numbering';

const { showFieldNumbers } = useFieldNumberingPreference();

const toggle = () => showFieldNumbers.value = !showFieldNumbers.value;
const label = computed(() => showFieldNumbers.value ? __('Hide field numbers') : __('Show field numbers'));
</script>

<template>
    <ToggleGroup :model-value="showFieldNumbers ? 'on' : null" size="xs">
        <ToggleItem
            value="on"
            icon="mail-sign-hashtag"
            :aria-label="label"
            v-tooltip="label"
            @click.prevent="toggle"
        />
    </ToggleGroup>
</template>
