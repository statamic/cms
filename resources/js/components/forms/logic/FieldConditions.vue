<script setup>
import { Icon } from '@/components/ui';
import FieldNumber from '@/components/forms/FieldNumber.vue';
import FieldConditionsBuilder from '@/components/field-conditions/Builder.vue';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories.js';

const emit = defineEmits(['update:conditions']);

const props = defineProps({
    conditions: { type: Object, default: () => ({}) },
    suggestableFields: { type: Array, default: () => [] },
    fieldtypes: Array,
});

const getFieldtypeCategory = (fieldtypeHandle) => {
    const fieldtype = props.fieldtypes?.find((field) => field.handle === fieldtypeHandle);
    const categoryKey = fieldtype?.categories?.[0] || 'other';
    return categories[categoryKey] ?? categories.other;
};

const fieldIconClasses = (fieldtypeHandle) => `size-4 shrink-0 ${categoryColorClasses[getFieldtypeCategory(fieldtypeHandle)?.color]?.icon}`;
const findSuggestableField = (handle) => props.suggestableFields.find((f) => f.handle === handle);

const onConditionsUpdated = (conditions) => emit('update:conditions', conditions);
const onAlwaysSaveUpdated = (alwaysSave) => emit('update:conditions', { ...props.conditions, always_save: alwaysSave });
</script>

<template>
    <FieldConditionsBuilder
        :config="conditions"
        :suggestable-fields
        :fieldtypes
        :allow-custom-conditions="false"
        :show-always-hide-option="true"
        :show-always-save="false"
        size="sm"
        @updated="onConditionsUpdated"
        @updated-always-save="onAlwaysSaveUpdated"
    >
        <template #field-option="{ value, label }">
            <span class="inline-flex items-center gap-2">
                <FieldNumber :field-key="value" />
                <Icon
                    v-if="findSuggestableField(value)?.icon"
                    :name="findSuggestableField(value).icon"
                    :class="fieldIconClasses(findSuggestableField(value)?.config?.type)"
                />
                <span class="truncate">{{ __(label) }}</span>
            </span>
        </template>
        <template #field-selected="{ option, field: selectedField }">
            <span class="inline-flex items-center gap-2 truncate">
                <FieldNumber :field-key="option.value" />
                <Icon
                    v-if="selectedField?.icon"
                    :name="selectedField.icon"
                    :class="fieldIconClasses(selectedField.config.type)"
                />
                <span class="truncate">{{ __(findSuggestableField(option.value)?.config?.display ?? option.value) }}</span>
            </span>
        </template>
    </FieldConditionsBuilder>
</template>
