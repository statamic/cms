<script setup>
import { Combobox, Input, Switch } from '@ui';
import { computed } from 'vue';
import HasInputOptions from '../fieldtypes/HasInputOptions.js';

const emit = defineEmits(['update:condition']);

const props = defineProps({
    condition: Object,
    suggestableFields: Array,
    excludeHandle: String,
    excludeOperators: { type: Array, default: () => [] },
    size: String,
});

const operatorOptions = computed(() => [
    { label: __('Equals'), value: 'equals' },
    { label: __('Does not equal'), value: 'not' },
    { label: __('Contains'), value: 'contains' },
    { label: __('Contains Any'), value: 'contains_any' },
    { label: '===', value: '===' },
    { label: '!==', value: '!==' },
    { label: '>', value: '>' },
    { label: '>=', value: '>=' },
    { label: '<', value: '<' },
    { label: '<=', value: '<=' },
    { label: __('Custom'), value: 'custom' },
].filter((op) => !props.excludeOperators.includes(op.value)));

const selectedField = computed(() => props.suggestableFields.find((field) => field.handle === props.condition.field));

const fieldOptions = computed(() => {
    return props.suggestableFields
        .filter((field) => field.handle !== props.excludeHandle && field.handle !== props.condition.field)
        .map((field) => {
            let display = field.config.display;

            if (!display) {
                display = field.handle.replace(/_/g, ' ').replace(/(?:^|\s)\S/g, function (a) {
                    return a.toUpperCase();
                });
            }

            return { value: field.handle, label: display };
        });
});

const isToggleField = (field) => ['toggle', 'revealer', 'yes_no'].includes(field?.config?.type);
const showValueToggle = computed(() => isToggleField(selectedField.value) && ['equals', 'not', '===', '!=='].includes(props.condition.operator));

const showValueDropdown = computed(() => {
    const optionTypes = ['button_group', 'checkboxes', 'radio', 'select', 'dropdown', 'multi_choice', 'ranking', 'image_choice'];
    return optionTypes.includes(selectedField.value?.config?.type) && ['equals', 'not', '===', '!=='].includes(props.condition.operator);
});

const showNumberInput = computed(() => {
    const optionTypes = ['number', 'integer', 'opinion_scale', 'star_ranking'];
    return optionTypes.includes(selectedField.value?.config?.type) && ['equals', 'not', '===', '!=='].includes(props.condition.operator);
});

const valueOptions = computed(() => {
    if (!showValueDropdown.value || !selectedField.value?.config.options) return null;
    return HasInputOptions.methods.normalizeInputOptions(selectedField.value.config.options);
});

const onFieldBlur = (search) => search ? update('field', search) : null;
const onValueBlur = (value) => value ? update('value', value) : null;
const onValueToggle = (checked) => update('value', checked.toString());

const update = (key, value) => {
    const condition = { ...props.condition, [key]: value };

    // When switching to a Toggle field, ensure the value is set to
    // "false" to ensure it doesn't get filtered out for being empty.
    if (
        key === 'field'
        && isToggleField(props.suggestableFields.find((field) => field.handle === value))
        && !condition.value
    ) {
        condition.value = 'false';
    }

    emit('update:condition', condition);
};
</script>

<template>
    <li>
        <Combobox
            :model-value="condition.field"
            :options="fieldOptions"
            option-label="label"
            option-value="value"
            :placeholder="__('Field')"
            :size
            searchable
            adaptive-width
            taggable
            @update:model-value="update('field', $event)"
            @search:blur="onFieldBlur"
        >
            <template v-if="$slots['field-option']" #option="optionProps">
                <slot name="field-option" v-bind="optionProps" />
            </template>
            <template v-if="$slots['field-selected']" #selected-option="{ option }">
                <slot name="field-selected" :option="option" :field="selectedField" />
            </template>
        </Combobox>
    </li>
    <li>
        <Combobox
            :model-value="condition.operator"
            :options="operatorOptions"
            option-label="label"
            option-value="value"
            :placeholder="__('Operator')"
            :searchable="false"
            :size
            adaptive-width
            @update:model-value="update('operator', $event)"
        />
    </li>
    <li>
        <Switch
            v-if="showValueToggle"
            :model-value="condition.value === 'true'"
            @update:model-value="onValueToggle"
        />
        <Combobox
            v-else-if="showValueDropdown"
            :model-value="condition.value"
            :options="valueOptions"
            option-label="label"
            option-value="value"
            :placeholder="__('Value')"
            :size
            searchable
            @update:model-value="update('value', $event)"
            @search:blur="onValueBlur"
        />
        <Input
            v-else
            :model-value="condition.value"
            :placeholder="__('Value')"
            :size
            :type="showNumberInput ? 'number' : 'text'"
            @update:model-value="update('value', $event)"
        />
    </li>
</template>
