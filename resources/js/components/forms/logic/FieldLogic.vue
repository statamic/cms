<script setup>
import { Button, Card, Heading, Icon, Panel, PanelHeader } from '@ui';
import AddLogicRuleButton from './AddLogicRuleButton.vue';
import FieldLogicRule from './FieldLogicRule.vue';
import { computed, nextTick, ref, watch } from 'vue';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';
import { KEYS } from '@/components/field-conditions/Constants.js';

const emit = defineEmits(['update:fields']);

const props = defineProps({
    fields: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});

const collapsed = ref([]);

const fieldHasLogic = (field) => field.hidden || KEYS.some(key => field[key]);

const fieldsWithLogic = computed(() => props.fields.filter(fieldHasLogic));
const fieldsWithoutLogic = computed(() => props.fields.filter(field => !fieldHasLogic(field)));

const expand = (id) => collapsed.value = collapsed.value.filter(setId => setId !== id);
const expandAll = () => collapsed.value = [];
const collapseAll = () => collapsed.value = fieldsWithLogic.value.map(field => field._id);

const collapse = (id) => {
    if (!collapsed.value.includes(id)) {
        collapsed.value.push(id);
    }
};

const allFieldsAreCollapsed = computed(() => fieldsWithLogic.value.every(field => collapsed.value.includes(field._id)));
const rulesView = computed(() => allFieldsAreCollapsed.value ? 'collapsed' : 'expanded');

const getIconClass = (category) => {
    const color = categories[category]?.color || 'gray';
    return categoryColorClasses[color]?.icon || 'text-gray-600 dark:text-gray-400';
};

const availableFields = computed(() => {
    return fieldsWithoutLogic.value.map(field => ({
        handle: field._id,
        display: field.display,
        icon: field.icon || 'generic-field',
        iconClass: getIconClass(field.category),
    }));
});

const getFieldConfig = (field) => ({
    handle: field._id,
    display: field.display,
    icon: field.icon || 'generic-field',
    iconClass: getIconClass(field.category),
});

const getConditionsConfig = (field) => ({
    handle: field.handle,
    hidden: field.hidden,
    if: field.if,
    unless: field.unless,
    if_any: field.if_any,
    unless_any: field.unless_any,
    always_save: field.always_save,
});

const getSuggestableFieldsForField = (field) => {
    return props.suggestableFields.filter(f => f.pageIndex <= field.pageIndex && f.handle !== field.handle);
};

const addCondition = (fieldId) => {
    const fields = props.fields.map(field =>
        field._id === fieldId ? { ...field, if: {} } : field
    );

    emit('update:fields', fields);

    nextTick(() => expand(fieldId));
};

const removeCondition = (fieldId) => {
    const fields = props.fields.map(field =>
        field._id === fieldId
            ? { ...field, if: null, unless: null, if_any: null, unless_any: null, always_save: false }
            : field
    );

    emit('update:fields', fields);
    collapsed.value = collapsed.value.filter(id => id !== fieldId);
};

const updateConditions = (fieldId, conditions) => {
    const fields = props.fields.map(field =>
        field._id === fieldId
            ? {
                ...field,
                hidden: conditions.hidden ?? field.hidden,
                if: conditions.if || null,
                unless: conditions.unless || null,
                if_any: conditions.if_any || null,
                unless_any: conditions.unless_any || null,
                always_save: conditions.always_save ?? field.always_save,
            }
            : field
    );

    emit('update:fields', fields);
};

watch(
    fieldsWithLogic,
    (fields, oldFields) => {
        const oldIds = new Set((oldFields || []).map(f => f._id));

        fields.forEach(field => {
            if (!oldIds.has(field._id)) {
                collapsed.value.push(field._id);
            }
        });
    },
    { immediate: true }
);
</script>

<template>
    <Panel>
        <PanelHeader>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <Icon name="form-text-field" class="size-4 text-gray-500 dark:text-gray-300" />
                    <Heading :text="__('Field Logic')" />
                </div>
                <div v-if="fieldsWithLogic.length > 0" class="flex items-center gap-2">
                    <Button
                        size="xs"
                        variant="ghost"
                        :icon="rulesView === 'collapsed' ? 'expand' : 'collapse'"
                        :aria-label="rulesView === 'collapsed' ? __('Expand all rules') : __('Collapse all rules')"
                        @click="rulesView === 'collapsed' ? expandAll() : collapseAll()"
                    />
                </div>
            </div>
        </PanelHeader>
        <Card>
            <div v-if="fieldsWithLogic.length > 0" class="relative space-y-6 mb-0" data-logic-list>
                <FieldLogicRule
                    v-for="field in fieldsWithLogic"
                    :id="field._id"
                    :key="field._id"
                    :config="getFieldConfig(field)"
                    :collapsed="collapsed.includes(field._id)"
                    :read-only="false"
                    :enabled="true"
                    :has-error="false"
                    :conditions="getConditionsConfig(field)"
                    :suggestable-fields="getSuggestableFieldsForField(field)"
                    :fieldtypes
                    @collapsed="collapse(field._id)"
                    @expanded="expand(field._id)"
                    @removed="removeCondition(field._id)"
                    @update:conditions="updateConditions(field._id, $event)"
                />
            </div>
            <AddLogicRuleButton
                v-if="availableFields.length > 0"
                :items="availableFields"
                :show-connector="fieldsWithLogic.length > 0"
                :label="__('Add Rule')"
                :search-placeholder="__('Search Fields')"
                @added="addCondition"
            />
        </Card>
    </Panel>
</template>

<style scoped>
[data-logic-list]::before {
    content: '';
    position: absolute;
    top: 1.5rem;
    bottom: 0;
    inset-inline-start: 0.875rem;
    border-inline-start: 1px dashed var(--color-gray-400);
}

.dark [data-logic-list]::before {
    border-inline-start-color: var(--color-gray-600);
}
</style>
