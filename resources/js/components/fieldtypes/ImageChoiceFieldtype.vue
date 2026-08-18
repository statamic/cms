<script setup>
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { ChoiceGrid } from '@/components/ui';
import { __ } from '@/bootstrap/globals';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly, name } = Fieldtype.use(emit, props);
defineExpose(expose);

const gaps = {
    2: 'sm',
    3: 'base',
    4: 'lg',
};

const multiple = computed(() => Boolean(props.config.multiple));
const label = computed(() => (props.config.display ? __(props.config.display) : null));
const gap = computed(() => gaps[props.config.gap] ?? 'base');

const options = computed(() =>
    (props.meta?.options ?? []).map((option) => ({
        value: option.key,
        label: option.label ? __(option.label) : null,
        image: option.image,
        badge: option.letter,
    })),
);

const selected = computed(() => (multiple.value ? props.value : (props.value?.[0] ?? null)));

function updateSelected(value) {
    update(multiple.value ? value : [value]);
}
</script>

<template>
    <ChoiceGrid
        :model-value="selected"
        :options
        :multiple
        :name
        :label
        :gap
        :columns="config.columns ?? 3"
        :aspect-ratio="config.aspect_ratio ?? '16/9'"
        :disabled="config.disabled || isReadOnly"
        @update:model-value="updateSelected"
    />
</template>
