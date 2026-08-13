<script setup>
import {
    Label,
    Combobox,
    Badge,
    Card,
    Panel,
    Icon,
} from '@ui';
import { computed, useId } from 'vue';
import Localization from './Localization.vue';

const props = defineProps({
    localizations: {
        type: Array,
        required: true,
    },
    localizing: {
        type: [Boolean, String],
        default: false,
    },
    label: {
        type: String,
        default: null,
    },
    heading: {
        type: String,
        default: null,
    },
});

defineEmits(['selected']);

const comboboxId = useId();

const panelHeading = computed(() => props.heading || __('Sites'));

const comboboxLabel = computed(() => props.label || props.heading || __('Current Localization'));

const activeLocalization = computed(() => {
    return props.localizations.find((localization) => localization.active);
});
</script>

<template>
    <Panel v-if="localizations.length > 1" :heading="panelHeading">
        <Card class="p-3! space-y-1">
            <template v-if="localizations.length > 5">
                <Label
                    :for="comboboxId"
                    :text="comboboxLabel"
                    :class="{ 'sr-only': heading }"
                />

                <Combobox
                    :id="comboboxId"
                    class="flex-1"
                    :options="localizations"
                    option-value="handle"
                    option-label="name"
                    :model-value="activeLocalization?.handle"
                    @update:modelValue="$emit('selected', localizations.find(l => l.handle === $event))"
                >
                    <template #option="option">
                        <Localization :localization="option" :localizing />
                    </template>
                </Combobox>
            </template>

            <div
                v-else
                role="group"
                :aria-label="panelHeading"
                class="space-y-1"
            >
                <button
                    v-for="option in localizations"
                    :key="option.handle"
                    class="w-full cursor-pointer px-4 py-2 text-sm rounded-lg"
                    :class="option.active ? 'dark:bg-gray-700 bg-blue-100' : 'dark:hover:bg-gray-800 hover:bg-gray-100'"
                    @click="$emit('selected', option)"
                >
                    <Localization :localization="option" :localizing />
                </button>
            </div>
        </Card>
    </Panel>
</template>
