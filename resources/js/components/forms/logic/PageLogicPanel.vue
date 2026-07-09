<script setup lang="ts">
import PageRule from '@/components/forms/builder/pages/PageRule.vue';
import { Button } from '@ui';
import { computed } from 'vue';
import { nanoid as uniqid } from 'nanoid';

const emit = defineEmits(['update:pages']);

const props = defineProps({
    page: { type: Object, required: true },
    pages: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});

const rules = computed(() => props.page.rules ?? []);
const pageIndex = computed(() => props.pages.findIndex((page) => page._id === props.page._id));
const suggestableFieldsForPage = computed(() => props.suggestableFields.filter((field) => field.pageIndex <= pageIndex.value));

const destinationOptions = computed(() =>
    props.pages.slice(pageIndex.value + 1).map((page, index) => ({
        label: page.display || __('Page :number', { number: pageIndex.value + index + 2 }),
        value: page._id,
        icon: 'page',
    })),
);

const canAddRule = computed(() => pageIndex.value < props.pages.length - 1);

const addRule = () => {
    updateRules([
        ...rules.value,
        {
            _id: uniqid(),
            conditions: [{ _id: uniqid(), field: null, operator: 'equals', value: null }],
            destination: null,
        },
    ]);
};

const updateRules = (rules) => {
    const pages = props.pages.map((page) => (page._id === props.page._id ? { ...page, rules } : page));

    emit('update:pages', pages);
};

const updateRule = (ruleId, updatedRule) => updateRules(rules.value.map((rule) => (rule._id === ruleId ? { ...updatedRule, _id: ruleId } : rule)));
const removeRule = (ruleId) => updateRules(rules.value.filter((rule) => rule._id !== ruleId));
</script>

<template>
    <div class="space-y-6">
        <div v-for="rule in rules" :key="rule._id" class="@container w-full">
            <PageRule
                :rule="rule"
                :suggestable-fields="suggestableFieldsForPage"
                :page-destination-options="destinationOptions"
                :fieldtypes
                @update:rule="updateRule(rule._id, $event)"
                @remove="removeRule(rule._id)"
            />
        </div>

        <Button v-if="canAddRule" :text="__('Add Rule')" icon="plus" size="sm" @click="addRule" />
    </div>
</template>
