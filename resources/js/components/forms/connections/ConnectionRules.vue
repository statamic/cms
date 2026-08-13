<script>
import { usePage } from '@inertiajs/vue3';

export function conditionsSummary(conditions) {
    const suggestableFields = usePage().props.suggestableFields;

    const operatorLabels = {
        equals: __('equals'),
        not: __('does not equal'),
        contains: __('contains'),
        contains_any: __('contains any of'),
    };

    const fieldDisplay = (handle) => {
        const field = suggestableFields.find((field) => field.handle === handle);
        return field?.config?.display ?? handle;
    };

    const filtered = (conditions ?? []).filter((condition) => condition.field);

    if (filtered.length === 0) return null;

    return filtered
        .map((condition, index) => {
            const prefix = index === 0 ? __('if') : __(condition.join === 'or' ? 'or' : 'and');
            const operator = operatorLabels[condition.operator] ?? condition.operator;
            return `${prefix} ${fieldDisplay(condition.field)} ${operator} ${condition.value ?? ''}`.trim();
        })
        .join(' ');
}
</script>

<script setup>
import { computed, ref } from 'vue';
import { Button, Combobox, Icon } from '@ui';
import { nanoid as uniqid } from 'nanoid';
import Condition from '@/components/field-conditions/Condition.vue';
import { categories, categoryColorClasses } from '@/components/forms/builder/categories';

const emit = defineEmits(['update:conditions']);

const props = defineProps({
    conditions: { type: Array, required: true },
    alwaysLabel: { type: String, default: () => __('Always run') },
    ifLabel: { type: String, default: () => __('Run if...') },
});

const suggestableFields = usePage().props.suggestableFields;

const when = ref(props.conditions.length ? 'if' : 'always');

const whenOptions = computed(() => [
    { label: props.alwaysLabel, value: 'always' },
    { label: props.ifLabel, value: 'if' },
]);

const joinOptions = [
    { label: __('All of the conditions pass'), short_label: __('And'), value: 'and' },
    { label: __('Any of the conditions pass'), short_label: __('Or'), value: 'or' },
];

const whenChanged = (value) => {
    when.value = value;

    if (value === 'always') {
        emit('update:conditions', []);
        return;
    }

    if (props.conditions.length === 0) {
        addCondition();
    }
};

const addCondition = () => {
    emit('update:conditions', [...props.conditions, {
        _id: uniqid(),
        join: 'and',
        field: null,
        operator: 'equals',
        value: null,
    }]);
};

const updateCondition = (index, condition) => {
    const conditions = [...props.conditions];
    conditions[index] = condition;
    emit('update:conditions', conditions);
};

const updateConditionJoin = (index, join) => {
    updateCondition(index, { ...props.conditions[index], join });
};

const removeCondition = (index) => {
    const conditions = [...props.conditions];
    conditions.splice(index, 1);
    emit('update:conditions', conditions);
};

const findSuggestableField = (handle) => suggestableFields.find((field) => field.handle === handle);

const fieldIconClasses = (handle) => {
    const color = categories[findSuggestableField(handle)?.category]?.color || 'gray';
    return `size-4 shrink-0 ${categoryColorClasses[color]?.icon || 'text-gray-600 dark:text-gray-400'}`;
};

const shouldBeIndented = (index) => {
    if (index === 0) return false;
    return props.conditions[index].join !== 'or';
};
</script>

<template>
    <div data-logic-text>
        <div class="logic-text">
            <ol>
                <li v-if="conditions.length === 0">
                    <div class="flex items-center">
                        <div class="logic-text__condition" aria-hidden="true">
                            <Combobox
                                :model-value="when"
                                class="min-w-34"
                                size="sm"
                                :options="whenOptions"
                                option-label="label"
                                option-value="value"
                                :searchable="false"
                                adaptive-width
                                @update:model-value="whenChanged($event)"
                            >
                                <template #selected-option="{ option }">
                                    <span class="block truncate">{{ option.value === 'if' ? __('If') : option.label }}</span>
                                </template>
                            </Combobox>
                        </div>
                    </div>

                    <ol v-if="when === 'if'">
                        <li>
                            <div class="relative">
                                <div class="absolute -z-1 -inset-y-2.5 -start-3.5 border-s-1 border-dashed border-gray-400 dark:border-gray-600" aria-hidden="true" />
                                <Button
                                    size="sm"
                                    variant="subtle"
                                    class="bg-transparent!"
                                    :text="__('+ Add Condition')"
                                    @click="addCondition"
                                />
                            </div>
                        </li>
                    </ol>
                </li>
                <li
                    v-for="(condition, index) in conditions"
                    :key="condition._id"
                    class="group/condition"
                    :class="{ 'ms-(--inner-indent) indented-condition': shouldBeIndented(index) }"
                >
                    <div class="flex items-center gap-0.25">
                        <div v-if="index === 0" class="logic-text__condition" aria-hidden="true">
                            <Combobox
                                :model-value="when"
                                class="min-w-34"
                                size="sm"
                                :options="whenOptions"
                                option-label="label"
                                option-value="value"
                                :searchable="false"
                                adaptive-width
                                @update:model-value="whenChanged($event)"
                            >
                                <template #selected-option="{ option }">
                                    <span class="block truncate">{{ option.value === 'if' ? __('If') : option.label }}</span>
                                </template>
                            </Combobox>
                        </div>
                        <div v-else class="logic-text__condition">
                            <Combobox
                                :model-value="condition.join || 'and'"
                                class="max-w-24"
                                size="sm"
                                :options="joinOptions"
                                option-label="label"
                                option-value="value"
                                :placeholder="__('And')"
                                :searchable="false"
                                adaptive-width
                                @update:model-value="updateConditionJoin(index, $event)"
                            >
                                <template #selected-option="{ option }">
                                    <span class="block truncate">{{ option.short_label }}</span>
                                </template>
                            </Combobox>
                        </div>
                        <div class="ms-0.5 mb-2.5 inline-flex items-center gap-1.5 opacity-0 pointer-events-none transition-opacity group-hover/condition:opacity-100 group-hover/condition:pointer-events-auto [@media(any-hover:none)]:opacity-100 [@media(any-hover:none)]:pointer-events-auto">
                            <Button
                                size="sm"
                                inset
                                variant="subtle"
                                class="size-7 rounded-full [&_div]:opacity-75 [&_div]:-translate-y-px"
                                :text="'&times;'"
                                :aria-label="__('Remove condition')"
                                @click="removeCondition(index)"
                            />
                        </div>
                    </div>

                    <ol>
                        <Condition
                            :condition="condition"
                            :conditions="conditions"
                            :suggestable-fields="suggestableFields"
                            :exclude-operators="['custom']"
                            size="sm"
                            @update:condition="updateCondition(index, $event)"
                        >
                            <template #field-option="{ value, label }">
                                <span class="inline-flex items-center gap-2">
                                    <Icon
                                        v-if="findSuggestableField(value)?.icon"
                                        :name="findSuggestableField(value).icon"
                                        :class="fieldIconClasses(value)"
                                    />
                                    <span class="truncate">{{ __(label) }}</span>
                                </span>
                            </template>
                            <template #field-selected="{ option }">
                                <span class="inline-flex items-center gap-2 truncate">
                                    <Icon
                                        v-if="findSuggestableField(option.value)?.icon"
                                        :name="findSuggestableField(option.value).icon"
                                        :class="fieldIconClasses(option.value)"
                                    />
                                    <span class="truncate">{{ __(findSuggestableField(option.value)?.config?.display ?? option.value) }}</span>
                                </span>
                            </template>
                        </Condition>
                    </ol>

                    <ol v-if="index === conditions.length - 1">
                        <li>
                            <div class="relative">
                                <div class="absolute -z-1 -top-6 -bottom-2.5 -start-3.5 w-4 rounded-ss-full border-s-1 border-t-1 border-dashed border-gray-400 dark:border-gray-600" aria-hidden="true" />
                                <Button
                                    size="sm"
                                    variant="subtle"
                                    class="bg-transparent!"
                                    :text="__('+ Add Condition')"
                                    @click="addCondition"
                                />
                            </div>
                        </li>
                    </ol>
                </li>
                <li v-if="$slots.then" data-hide-connector>
                    <div v-if="when === 'if'" class="logic-text-badge logic-text__condition" aria-hidden="true">
                        {{ __('Then') }}
                    </div>
                    <ol>
                        <li class="w-full min-w-0"><slot name="then" /></li>
                    </ol>
                </li>
            </ol>
        </div>
    </div>
</template>

<style scoped>
[data-hide-connector]::before {
    content: none !important;
}

.logic-text__condition::after {
    z-index: var(--z-index-below);
}
</style>
