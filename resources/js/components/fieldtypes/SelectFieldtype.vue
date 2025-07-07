<template>
    <Combobox
        class="w-full"
        :options="options"
        :clearable="config.clearable"
        :placeholder="__(config.placeholder)"
        :multiple="config.multiple"
        :searchable="config.searchable || config.taggable"
        :taggable="config.taggable"
        :disabled="config.disabled || isReadOnly"
        :max-selections="config.max_items"
        :label-html="config.label_html"
        :model-value="value"
        @update:modelValue="comboboxUpdated"
    />
</template>

<script setup>
import { Fieldtype } from 'statamic';
import HasInputOptions from './HasInputOptions.js';
import { SortableList } from '../sortable/Sortable';
import { Badge, Combobox } from '@statamic/ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, defineReplicatorPreview, update } = Fieldtype.use(emit, props);

const selectedOptions = computed(() => {
    let selections = props.value === null ? [] : props.value;

    if (typeof selections === 'string' || typeof selections === 'number') {
        selections = [selections];
    }

    return selections.map((value) => {
        return options.value.find((option) => option.value === value) ?? { label: value, value };
    });
});

const options = computed(() => {
    return HasInputOptions.methods.normalizeInputOptions(props.meta.options || props.config.options);
});

defineReplicatorPreview(() => selectedOptions.value.map((option) => option.label).join(', '));

function comboboxUpdated(value) {
    update(value || null);
}
</script>
