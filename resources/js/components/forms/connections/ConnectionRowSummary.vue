<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Badge, Icon, Subheading } from '@ui';
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';
import { operatorLabel } from '@/components/forms/logic/operatorLabels';
import { __ } from '@/bootstrap/globals';

type Condition = {
    field: string | null;
    operator: string;
    value: unknown;
    join?: 'and' | 'or';
};

type SuggestableField = {
    handle: string;
    icon?: string;
    category?: string;
    config?: { display?: string };
};

enum PartType {
    Join = 'join',
    Field = 'field',
    Operator = 'operator',
    Value = 'value',
}

type SummaryPart = {
    type: PartType;
    text: string;
};

const props = withDefaults(defineProps<{
    conditions?: Condition[];
    fallback?: string | null;
}>(), {
    conditions: () => [],
    fallback: null,
});

const suggestableFields = usePage().props.suggestableFields as SuggestableField[];

const findField = (handle: string): SuggestableField | undefined => suggestableFields.find((field) => field.handle === handle);
const fieldDisplay = (handle: string): string => __(findField(handle)?.config?.display) || handle;

const fieldIconClass = (field?: SuggestableField): string => {
    const color = categories[field?.category]?.color || 'gray';

    return categoryColorClasses[color]?.icon || 'text-gray-600 dark:text-gray-400';
};

const completeConditions = computed(() => props.conditions.filter((condition) => condition.field));

const firstField = computed(() => {
    const handle = completeConditions.value[0]?.field;

    if (!handle) return null;

    const field = findField(handle);

    return {
        handle,
        display: fieldDisplay(handle),
        icon: field?.icon || 'generic-field',
        iconClass: fieldIconClass(field),
    };
});

const hasValue = (condition: Condition): boolean => condition.value !== null && condition.value !== undefined && condition.value !== '';
const displayValue = (condition: Condition): string => (Array.isArray(condition.value) ? condition.value.join(', ') : String(condition.value));

const previewParts = computed<SummaryPart[]>(() =>
    completeConditions.value.flatMap((condition, index) => {
        const parts: SummaryPart[] = [];

        if (index > 0) {
            parts.push({ type: PartType.Join, text: condition.join === 'or' ? __('or') : __('and') });
            parts.push({ type: PartType.Field, text: fieldDisplay(condition.field) });
        }

        parts.push({ type: PartType.Operator, text: operatorLabel(condition.operator) });

        if (hasValue(condition)) {
            parts.push({ type: PartType.Value, text: displayValue(condition) });
        }

        return parts;
    }),
);
</script>

<template>
    <Badge v-if="completeConditions.length" pill size="sm" color="white" class="font-medium text-gray-800 dark:text-gray-200">
        {{ __('If') }}
    </Badge>
    <Badge v-if="firstField" pill color="white" class="ps-1.5 py-1 text-gray-950 gap-1">
        <FieldNumber :field-key="firstField.handle" class="me-0.5" />
        <Icon
            :name="firstField.icon"
            class="size-3.5 me-1 rounded-sm opacity-100!"
            :class="firstField.iconClass"
            aria-hidden="true"
        />
        <span class="st-text-trim-cap">{{ firstField.display }}</span>
    </Badge>
    <Subheading class="overflow-hidden text-ellipsis whitespace-nowrap text-xs flex items-center gap-1">
        <span v-if="completeConditions.length === 0" class="lowercase">{{ fallback || __('Always') }}</span>
        <template v-else>
            <template v-for="(part, index) in previewParts" :key="index">
                <Badge
                    v-if="part.type === PartType.Operator || part.type === PartType.Join"
                    class="inline-block px-1 py-1.5 font-medium st-text-trim-ex-alphabetic lowercase bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300"
                >
                    {{ part.text }}
                </Badge>
                <span v-else-if="part.type === PartType.Value" class="font-mono text-gray-900 dark:text-gray-100">{{ part.text }}</span>
                <span v-else class="text-gray-700 dark:text-gray-300">{{ part.text }}</span>
            </template>
        </template>
    </Subheading>
</template>
