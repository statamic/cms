<script setup>
import { Button, Combobox, Icon } from '@ui';
import { computed } from 'vue';
import { nanoid as uniqid } from 'nanoid';
import Condition from '@/components/field-conditions/Condition.vue';
import FieldNumber from '@/components/forms/FieldNumber.vue';
import { categories, categoryColorClasses } from '../categories';

const emit = defineEmits(['update:rule', 'remove']);

const props = defineProps({
    rule: { type: Object, required: true },
    suggestableFields: { type: Array, required: true },
    pageDestinationOptions: { type: Array, required: true },
    fieldtypes: { type: Array, default: () => [] },
});

const joinOptions = [
    { label: __('All of the conditions pass'), short_label: __('And'), value: 'and' },
    { label: __('Any of the conditions pass'), short_label: __('Or'), value: 'or' },
];

const conditions = computed({
    get: () => props.rule.conditions,
    set: (value) => update('conditions', value),
});

const destination = computed({
    get: () => props.rule.destination,
    set: (value) => update('destination', value),
});

const destinationPageIsMissing = computed(() => {
    if (!destination.value) return false;
    return !props.pageDestinationOptions.find(option => option.value === destination.value);
});

const update = (key, value) => emit('update:rule', { ...props.rule, [key]: value });

const addCondition = () => {
    const newConditions = [...conditions.value, {
        _id: uniqid(),
        join: 'and',
        field: null,
        operator: 'equals',
        value: null,
    }];

    update('conditions', newConditions);
};

const updateCondition = (index, condition) => {
    const newConditions = [...conditions.value];
    newConditions[index] = condition;
    update('conditions', newConditions);
};

const updateConditionJoin = (index, join) => {
    const newConditions = [...conditions.value];
    newConditions[index] = { ...newConditions[index], join };
    update('conditions', newConditions);
};

const removeCondition = (index) => {
    const newConditions = [...conditions.value];
    newConditions.splice(index, 1);
    update('conditions', newConditions);
};

const getFieldtypeCategory = (fieldtypeHandle) => {
    const fieldtype = props.fieldtypes?.find((field) => field.handle === fieldtypeHandle);
    const categoryKey = fieldtype?.categories?.[0] || 'other';
    return categories[categoryKey] ?? categories.other;
};

const fieldIconClasses = (fieldtypeHandle) => `size-4 shrink-0 ${categoryColorClasses[getFieldtypeCategory(fieldtypeHandle)?.color]?.icon || 'text-gray-600 dark:text-gray-400'}`;
const findSuggestableField = (handle) => props.suggestableFields.find((f) => f.handle === handle);

const shouldBeIndented = (index) => {
    if (index === 0) return false;
    const condition = conditions.value[index];
    return condition.join === 'and';
};
</script>

<template>
    <div class="group/rule" data-logic-text>
        <div class="logic-text">
            <ol>
                <li
                    v-for="(condition, index) in conditions"
                    :key="condition._id"
                    :class="{ 'ms-(--inner-indent) indented-condition': shouldBeIndented(index) }"
                >
                    <template v-if="index === 0">
                        <div class="flex items-center">
                            <div class="logic-text-badge logic-text__condition" aria-hidden="true">
                                {{ __('If') }}
                            </div>
                            <div
                                class="ms-0.5 mb-2.5 inline-flex items-center gap-1.5 opacity-0 pointer-events-none transition-opacity group-hover/rule:opacity-100 group-hover/rule:pointer-events-auto [@media(any-hover:none)]:opacity-100 [@media(any-hover:none)]:pointer-events-auto"
                            >
                                <Button
                                    size="sm"
                                    inset
                                    variant="subtle"
                                    class="size-7 [&_svg]:opacity-50 rounded-full ms-[0.025rem]"
                                    icon="trash"
                                    :aria-label="__('Delete rule')"
                                    @click="$emit('remove')"
                                />
                            </div>
                        </div>
                    </template>

                    <template v-else>
                        <div class="flex items-center gap-0.25">
                            <div class="logic-text__condition">
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
                            <div class="ms-0.5 mb-2.5 inline-flex items-center gap-1.5 opacity-0 pointer-events-none transition-opacity group-hover/rule:opacity-100 group-hover/rule:pointer-events-auto [@media(any-hover:none)]:opacity-100 [@media(any-hover:none)]:pointer-events-auto">
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
                    </template>

                    <ol>
                        <Condition
                            :condition="condition"
                            :suggestable-fields="suggestableFields"
                            :exclude-operators="['custom']"
                            size="sm"
                            @update:condition="updateCondition(index, $event)"
                        >
                            <template #field-option="{ value, label }">
                                <span class="inline-flex items-center gap-2">
                                    <FieldNumber :field-key="value" />
                                    <Icon
                                        v-if="findSuggestableField(value)?.icon"
                                        :name="findSuggestableField(value).icon"
                                        :class="fieldIconClasses(findSuggestableField(value).config.type)"
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
                        </Condition>
                    </ol>
                </li>

                <li>
                    <div class="logic-text-badge logic-text__condition" aria-hidden="true">
                        {{ __('Then') }}
                    </div>
                    <ol>
<!--                        <li>-->
<!--                            <Dropdown>-->
<!--                                <template #trigger>-->
<!--                                    <button-->
<!--                                        type="button"-->
<!--                                        class="w-full min-w-0 flex items-center justify-between rounded-lg border border-gray-300 bg-linear-to-b from-white to-gray-50 px-3 h-8 text-sm text-gray-900 shadow-ui-sm dark:from-gray-850 dark:to-gray-900 dark:border-gray-700 dark:text-gray-300 dark:shadow-ui-md"-->
<!--                                    >-->
<!--                                        <span class="truncate">{{ branchingActionLabel }}</span>-->
<!--                                        <Icon name="chevron-down" class="ms-2 size-4 text-gray-400 dark:text-white/40" />-->
<!--                                    </button>-->
<!--                                </template>-->
<!--                                <DropdownMenu>-->
<!--                                    <DropdownLabel :text="__('Logic')" />-->
<!--                                    <DropdownItem :text="__('Go to')" @click="logicBranchingAction = 'go_to'" />-->
<!--                                    <DropdownSeparator />-->
<!--                                    <DropdownLabel :text="__('Calculation')" />-->
<!--                                    <DropdownItem @click="logicBranchingAction = 'add'">-->
<!--                                    <span class="inline-flex items-center gap-2">-->
<!--                                        <span>+</span>-->
<!--                                        <span>{{ __('Add') }}</span>-->
<!--                                    </span>-->
<!--                                    </DropdownItem>-->
<!--                                    <DropdownItem @click="logicBranchingAction = 'divide'">-->
<!--                                    <span class="inline-flex items-center gap-2">-->
<!--                                        <span>÷</span>-->
<!--                                        <span>{{ __('Divide') }}</span>-->
<!--                                    </span>-->
<!--                                    </DropdownItem>-->
<!--                                    <DropdownItem @click="logicBranchingAction = 'subtract'">-->
<!--                                    <span class="inline-flex items-center gap-2">-->
<!--                                        <span>−</span>-->
<!--                                        <span>{{ __('Subtract') }}</span>-->
<!--                                    </span>-->
<!--                                    </DropdownItem>-->
<!--                                    <DropdownItem @click="logicBranchingAction = 'multiply'">-->
<!--                                    <span class="inline-flex items-center gap-2">-->
<!--                                        <span>×</span>-->
<!--                                        <span>{{ __('Multiply') }}</span>-->
<!--                                    </span>-->
<!--                                    </DropdownItem>-->
<!--                                </DropdownMenu>-->
<!--                            </Dropdown>-->
<!--                        </li>-->
                        <li>
                            <div class="logic-text-badge" aria-hidden="true">
                                {{ __('Go to') }}
                            </div>
                        </li>
                        <li>
                            <Combobox
                                v-model="destination"
                                size="sm"
                                variant="default"
                                :options="pageDestinationOptions"
                                option-label="label"
                                option-value="value"
                                :placeholder="__('Select a page')"
                                searchable
                            >
                                <template #option="{ icon, label }">
                                    <div class="flex min-w-0 gap-2 items-center text-left">
                                        <Icon
                                            v-if="icon"
                                            :name="icon"
                                            class="size-4 shrink-0 text-gray-500 dark:text-gray-300"
                                        />
                                        <span class="block truncate">{{ label }}</span>
                                    </div>
                                </template>
                                <template #selected-option="{ option }">
                                    <div v-if="destinationPageIsMissing" class="flex min-w-0 items-center gap-1 -ms-0.75">
                                        <div class="flex shrink-0 items-center justify-center size-6 text-red-600 dark:text-red-500">
                                            <Icon name="trash" class="size-4 shrink-0 text-red-600 dark:text-red-500" />
                                        </div>
                                        <span class="block truncate text-red-600 dark:text-red-500">{{ __('Deleted page') }}</span>
                                    </div>
                                    <div v-else class="flex min-w-0 items-center gap-1 -ms-0.75">
                                        <div class="flex shrink-0 items-center justify-center size-6 text-gray-500 dark:text-gray-300">
                                            <Icon
                                                v-if="option.icon"
                                                :name="option.icon"
                                                class="size-4 shrink-0 text-gray-500 dark:text-gray-300"
                                            />
                                        </div>
                                        <span class="block truncate">{{ option.label }}</span>
                                    </div>
                                </template>
                            </Combobox>
                        </li>
                    </ol>
                </li>
            </ol>

            <Button
                size="sm"
                variant="subtle"
                class="ms-4 bg-transparent!"
                :text="__('+ Add Condition')"
                @click="addCondition"
            />
        </div>
    </div>
</template>
