<script setup lang="ts">
import FieldConditions from '@/components/forms/logic/FieldConditions.vue';
import PageRule from '@/components/forms/builder/pages/PageRule.vue';
import { Button } from '@ui';
import { computed } from 'vue';
import { nanoid as uniqid } from 'nanoid';

const emit = defineEmits(['update:fields', 'update:pages']);

const props = defineProps({
    selection: { type: Object, default: null }, // { type: 'field' | 'page', id }
    fields: { type: Array, required: true },
    pages: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});

// A field's own show/hide logic.
const selectedField = computed(() =>
    props.selection?.type === 'field' ? props.fields.find((field) => field._id === props.selection.id) ?? null : null,
);

const fieldConditions = computed(() => {
    const field = selectedField.value;
    if (! field) return {};

    return {
        handle: field.handle,
        hidden: field.hidden,
        if: field.if,
        unless: field.unless,
        if_any: field.if_any,
        unless_any: field.unless_any,
        always_save: field.always_save,
    };
});

const suggestableFieldsForField = computed(() => {
    const field = selectedField.value;
    if (! field) return [];

    return props.suggestableFields.filter((f) => f.pageIndex <= field.page_index && f.handle !== field.handle);
});

const updateConditions = (conditions) => {
    const fields = props.fields.map((field) =>
        field._id === selectedField.value._id
            ? {
                ...field,
                hidden: conditions.hidden ?? field.hidden,
                if: conditions.if || null,
                unless: conditions.unless || null,
                if_any: conditions.if_any || null,
                unless_any: conditions.unless_any || null,
                always_save: conditions.always_save ?? field.always_save,
            }
            : field,
    );

    emit('update:fields', fields);
};

// A page's routing rules (if this page's fields match, go to another page).
const selectedPage = computed(() =>
    props.selection?.type === 'page' ? props.pages.find((page) => page._id === props.selection.id) ?? null : null,
);

const selectedPageIndex = computed(() =>
    selectedPage.value ? props.pages.findIndex((page) => page._id === selectedPage.value._id) : -1,
);

const pageRules = computed(() => selectedPage.value?.rules ?? []);

const suggestableFieldsForPage = computed(() =>
    props.suggestableFields.filter((field) => field.pageIndex <= selectedPageIndex.value),
);

const pageDestinationOptions = computed(() =>
    props.pages.slice(selectedPageIndex.value + 1).map((page, index) => ({
        label: page.display || __('Page :number', { number: selectedPageIndex.value + index + 2 }),
        value: page._id,
        icon: 'page',
    })),
);

const canAddRule = computed(() => selectedPageIndex.value < props.pages.length - 1);

const updatePageRules = (rules) => {
    const pages = props.pages.map((page) => (page._id === selectedPage.value._id ? { ...page, rules } : page));

    emit('update:pages', pages);
};

const addRule = () => {
    updatePageRules([
        ...pageRules.value,
        {
            _id: uniqid(),
            conditions: [{ _id: uniqid(), field: null, operator: 'equals', value: null }],
            destination: null,
        },
    ]);
};

const updateRule = (ruleId, updatedRule) =>
    updatePageRules(pageRules.value.map((rule) => (rule._id === ruleId ? { ...updatedRule, _id: ruleId } : rule)));

const removeRule = (ruleId) => updatePageRules(pageRules.value.filter((rule) => rule._id !== ruleId));
</script>

<template>
    <div class="mx-auto max-w-3xl p-6">
        <FieldConditions
            v-if="selectedField"
            :key="selectedField._id"
            :conditions="fieldConditions"
            :suggestable-fields="suggestableFieldsForField"
            :fieldtypes
            @update:conditions="updateConditions"
        />

        <div v-else-if="selectedPage" class="space-y-6">
            <PageRule
                v-for="rule in pageRules"
                :key="rule._id"
                :rule="rule"
                :suggestable-fields="suggestableFieldsForPage"
                :page-destination-options="pageDestinationOptions"
                :fieldtypes
                @update:rule="updateRule(rule._id, $event)"
                @remove="removeRule(rule._id)"
            />
            <Button v-if="canAddRule" :text="__('Add Rule')" icon="plus" size="sm" @click="addRule" />
        </div>
    </div>
</template>
