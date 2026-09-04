<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Badge, Icon, Subheading } from '@ui';
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';

const props = defineProps({
    conditions: { type: Array, default: () => [] },
    fallback: { type: String, default: null },
});

const suggestableFields = usePage().props.suggestableFields ?? [];

const operatorLabels = {
    '': __('equals'),
    equals: __('equals'),
    not: __('does not equal'),
    contains: __('contains'),
    contains_any: __('contains any of'),
    '==': __('equals'),
    '!=': __('does not equal'),
    '>': __('is greater than'),
    '<': __('is less than'),
    '>=': __('is at least'),
    '<=': __('is at most'),
};

const getOperatorLabel = (operator) => operatorLabels[operator] || operator || __('equals');
const getFieldConfig = (handle) => suggestableFields.find((field) => field.handle === handle);
const getFieldDisplay = (handle) => __(getFieldConfig(handle)?.config?.display) || handle;
const getIconClass = (category) => {
    const color = categories[category]?.color || 'gray';
    return categoryColorClasses[color]?.icon || 'text-gray-600 dark:text-gray-400';
};

const filteredConditions = computed(() => (props.conditions ?? []).filter((condition) => condition.field));

const firstFieldConfig = computed(() => {
    const firstCondition = filteredConditions.value[0];
    if (!firstCondition?.field) return null;

    const field = getFieldConfig(firstCondition.field);

    return {
        handle: firstCondition.field,
        display: __(field?.config?.display) || firstCondition.field,
        icon: field?.icon || 'generic-field',
        iconClass: getIconClass(field?.category),
    };
});

const previewParts = computed(() => {
    if (filteredConditions.value.length === 0) return null;

    const parts = [];

    filteredConditions.value.forEach((condition, index) => {
        if (index === 0) {
            parts.push({ type: 'operator', text: getOperatorLabel(condition.operator) });

            if (condition.value !== null && condition.value !== undefined && condition.value !== '') {
                const displayValue = Array.isArray(condition.value)
                    ? condition.value.join(', ')
                    : String(condition.value);
                parts.push({ type: 'value', text: displayValue });
            }

            return;
        }

        parts.push({ type: 'join', text: condition.join === 'or' ? __('or') : __('and') });
        parts.push({ type: 'field-plain', text: getFieldDisplay(condition.field) });
        parts.push({ type: 'operator', text: getOperatorLabel(condition.operator) });

        if (condition.value !== null && condition.value !== undefined && condition.value !== '') {
            const displayValue = Array.isArray(condition.value)
                ? condition.value.join(', ')
                : String(condition.value);
            parts.push({ type: 'value', text: displayValue });
        }
    });

    return parts.length ? parts : null;
});

const collapsedSummary = computed(() => {
    if (filteredConditions.value.length === 0) {
        return props.fallback || __('Always');
    }

    if (!previewParts.value) return __('Configure conditions');

    return null;
});
</script>

<template>
    <Badge v-if="filteredConditions.length" pill size="sm" color="white" class="ms-1 font-medium text-gray-800 dark:text-gray-200">
        {{ __('If') }}
    </Badge>
    <Badge v-if="firstFieldConfig" size="lg" pill color="white" class="px-3 text-gray-950 gap-1">
        <FieldNumber :field-key="firstFieldConfig.handle" class="me-0.5" />
        <Icon
            :name="firstFieldConfig.icon"
            class="size-3.5 me-1 rounded-sm opacity-100!"
            :class="firstFieldConfig.iconClass"
            aria-hidden="true"
        />
        {{ firstFieldConfig.display }}
    </Badge>
    <Subheading class="overflow-hidden text-ellipsis whitespace-nowrap text-xs flex items-center gap-1">
        <template v-if="collapsedSummary">
            <span class="lowercase">{{ collapsedSummary }}</span>
        </template>
        <template v-else-if="previewParts">
            <template v-for="(part, index) in previewParts" :key="index">
                <Badge
                    v-if="part.type === 'operator'"
                    class="inline-block px-1 py-1.5 font-medium st-text-trim-ex-alphabetic lowercase bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                >
                    {{ part.text }}
                </Badge>
                <span v-else-if="part.type === 'value'" class="font-mono text-gray-900 dark:text-gray-100">{{ part.text }}</span>
                <Badge
                    v-else-if="part.type === 'join'"
                    class="inline-block px-1 py-1.5 font-medium st-text-trim-ex-alphabetic lowercase bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                >
                    {{ part.text }}
                </Badge>
                <span v-else-if="part.type === 'field-plain'" class="text-gray-700 dark:text-gray-300">{{ part.text }}</span>
            </template>
        </template>
    </Subheading>
</template>
