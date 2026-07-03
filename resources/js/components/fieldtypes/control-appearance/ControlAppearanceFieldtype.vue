<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import Preview from './Preview.vue';
import { Button, ButtonGroup } from '@/components/ui';
import { computed, ref } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { name, isReadOnly, update, expose } = Fieldtype.use(emit, props);

const buttons = ref([]);

const control = computed(() => props.config.control || 'radio');

const options = computed(() => [
    { value: 'default', label: __('Default') },
    { value: 'inline', label: __('Inline') },
    { value: 'chips', label: __('Chips') },
]);

function buttonClass(optionValue) {
    const base = 'min-w-34 h-auto items-start justify-start py-1.5';

    if (props.value != optionValue) {
        return [
            base,
            'from-white to-white hover:from-white hover:to-gray-50',
            'dark:from-gray-850 dark:to-gray-850 dark:hover:from-gray-800 dark:hover:to-gray-850',
        ];
    }

    return [
        base,
        'from-gray-150 to-gray-50 border-gray-300',
        'dark:from-gray-925 dark:to-gray-900 dark:border-gray-700/80',
    ];
}

function focus() {
    buttons.value[0]?.focus();
}

defineExpose({ ...expose, focus });
</script>

<template>
    <ButtonGroup overflow="gap">
        <Button
            v-for="option in options"
            ref="buttons"
            :key="option.value"
            :disabled="config.disabled"
            :name="name"
            :read-only="isReadOnly"
            :value="option.value"
            :variant="value == option.value ? 'pressed' : 'default'"
            :class="buttonClass(option.value)"
            @click="update(option.value)"
        >
            <div class="flex w-full flex-col items-start gap-2 py-0.5 text-left">
                <span class="text-sm font-medium">{{ option.label }}</span>
                <Preview :appearance="option.value" :control="control" />
            </div>
        </Button>
    </ButtonGroup>
</template>
