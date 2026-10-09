<script setup>
import { Button } from '@ui';
import { nanoid as uniqid } from 'nanoid';
import PageRule from './PageRule.vue';

const emit = defineEmits(['update:rules']);

const props = defineProps({
    rules: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    pageDestinationOptions: { type: Array, required: true },
    fieldtypes: { type: Array, default: () => [] },
});

const updateRule = (index, rule) => {
    const newRules = [...props.rules];
    newRules[index] = rule;
    emit('update:rules', newRules);
};

const addRule = () => {
    const defaultDestination = props.pageDestinationOptions.length > 0 ? props.pageDestinationOptions[0].value : null;

    const newRules = [...props.rules, {
        _id: uniqid(),
        conditions: [{
            _id: uniqid(),
            field: null,
            operator: 'equals',
            value: null,
        }],
        destination: defaultDestination,
    }];

    emit('update:rules', newRules);
};

const removeRule = (index) => {
    const newRules = [...props.rules];
    newRules.splice(index, 1);
    emit('update:rules', newRules);
};
</script>

<template>
    <div class="group/logic-tab space-y-6">
        <template v-for="(rule, index) in rules" :key="rule._id">
            <div
                v-if="index > 0"
                class="my-6 border-t border-dashed border-gray-400 dark:border-gray-700"
            />

            <PageRule
                :rule="rule"
                :suggestable-fields
                :page-destination-options
                :fieldtypes
                @update:rule="updateRule(index, $event)"
                @remove="removeRule(index)"
            />
        </template>

        <div class="mt-8 mb-6 pt-4 border-t border-dashed border-gray-300 dark:border-gray-700">
            <Button size="sm" :text="__('+ Add Rule')" @click="addRule" />
        </div>
    </div>
</template>
