<template>
    <Combobox
        class="w-full"
        :clearable="config.clearable"
        :disabled="config.disabled"
        :label-html="config.label_html"
        :max-selections="config.max_items"
        :model-value="value"
        :multiple="config.multiple"
        :options="options"
        :placeholder="__(config.placeholder)"
        :read-only="isReadOnly"
        :searchable="config.searchable || config.taggable"
        :taggable="config.taggable"
        :id="id"
        @update:modelValue="comboboxUpdated"
    />
</template>

<script setup>
import { Fieldtype } from 'statamic';
import HasInputOptions from './HasInputOptions.js';
import { Combobox } from '@statamic/cms/ui';
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
