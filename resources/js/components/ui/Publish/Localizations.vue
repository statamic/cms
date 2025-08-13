<script setup>
import { Label, Combobox, Badge, Card, Panel, Icon } from '@statamic/ui';
import { computed } from 'vue';
import Localization from '@statamic/components/ui/Publish/Localization.vue';

const props = defineProps({
    localizations: {
        type: Array,
        required: true,
    },
    localizing: {
        type: [Boolean, String],
        default: false,
    },
});

defineEmits(['selected']);

const activeLocalization = computed(() => {
    return props.localizations.find((localization) => localization.active);
});
</script>

<template>
    <Panel v-if="localizations.length > 1" :heading="__('Sites')">
        <Card class="p-3! space-y-1">
            <template v-if="localizations.length > 5">
                <Label :text="__('Current Localization')" />

                <Combobox
                    class="flex-1"
                    :options="localizations"
                    option-value="handle"
                    option-label="name"
                    :model-value="activeLocalization?.handle"
                    @update:modelValue="$emit('selected', $event)"
                >
                    <template #option="option">
                        <Localization :localization="option" :localizing />
                    </template>
                </Combobox>
            </template>

            <button
                v-else
                v-for="option in localizations"
                :key="option.handle"
                class="w-full cursor-pointer px-4 py-2 text-sm rounded-lg"
                :class="option.active ? 'dark:bg-gray-700 bg-blue-100' : 'dark:hover:bg-gray-800 hover:bg-gray-100'"
                @click="$emit('selected', option)"
            >
                <Localization :localization="option" :localizing />
            </button>
        </Card>
    </Panel>
</template>
