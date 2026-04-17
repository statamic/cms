<script setup>
import { Combobox, Icon } from '@ui';
import { ref } from 'vue';

const logicOperator = ref('equals');
const logicOperatorOptions = [
    { label: __('Equals'), value: 'equals' },
    { label: __('Does not equal'), value: 'not_equals' },
    { label: __('Contains'), value: 'contains' },
    { label: __('Is empty'), value: 'is_empty' },
];

const logicValue = ref('days_of_thunder');
const logicValueOptions = [
    { label: __('Days of Thunder'), value: 'days_of_thunder' },
    { label: __('Endless Summer'), value: 'endless_summer' },
    { label: __('Nocturnal'), value: 'nocturnal' },
    { label: __('Kids'), value: 'kids' },
];

const logicDestination = ref('second_favorite');
const logicDestinationOptions = [
    { label: __('And second favorite album?'), value: 'second_favorite', icon: 'fieldtype-radio' },
    { label: __('How long have you been a fan?'), value: 'fan_length', icon: 'text-short' },
    { label: __('Sign up for email notifications'), value: 'email_notifications', icon: 'fieldtype-checkboxes' },
];
</script>

<template>
    <div data-logic-tree class="logic-flow">
        <h3 class="sr-only">{{ __('Conditional logic') }}</h3>

        <ol class="logic-flow__root">
            <li class="logic-flow__branch">
                <div class="logic-flow__branch-inner">
                    <div class="logic-flow__if-knot" aria-hidden="true">
                        <span class="logic-flow__if-badge">{{ __('If') }}</span>
                    </div>
                    <ol class="logic-flow__nest">
                        <li class="logic-flow__row">
                            <div
                                class="logic-flow__pill flex items-center gap-2 rounded-full border border-gray-300 bg-white px-3 py-2 shadow-ui-sm dark:border-gray-600 dark:bg-gray-900"
                            >
                                <span
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full bg-orange-500/15 text-orange-600 dark:bg-orange-500/25 dark:text-orange-400"
                                >
                                    <Icon name="fieldtype-radio" class="size-3.5" />
                                </span>
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ __('Which album was your favorite?') }}
                                </span>
                            </div>
                        </li>
                        <li class="logic-flow__row">
                            <Combobox
                                v-model="logicOperator"
                                class="max-w-44"
                                size="sm"
                                variant="default"
                                :options="logicOperatorOptions"
                                option-label="label"
                                option-value="value"
                                :placeholder="__('Operator')"
                                :searchable="false"
                            />
                        </li>
                        <li class="logic-flow__row">
                            <Combobox
                                v-model="logicValue"
                                class="max-w-xs"
                                size="sm"
                                variant="default"
                                :options="logicValueOptions"
                                option-label="label"
                                option-value="value"
                                :placeholder="__('Value')"
                                searchable
                            />
                        </li>
                    </ol>
                </div>
            </li>

            <li class="logic-flow__branch">
                <div class="logic-flow__branch-inner">
                    <div class="logic-flow__if-knot" aria-hidden="true" />
                    <ol class="logic-flow__nest">
                        <li class="logic-flow__row">
                            <div
                                class="logic-flow__pill logic-flow__pill--muted inline-flex rounded-full border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 shadow-ui-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200"
                            >
                                {{ __('Then go to …') }}
                            </div>
                        </li>
                        <li class="logic-flow__row">
                            <Combobox
                                v-model="logicDestination"
                                class="max-w-md"
                                size="sm"
                                variant="default"
                                :options="logicDestinationOptions"
                                option-label="label"
                                option-value="value"
                                :placeholder="__('Destination')"
                                searchable
                            >
                                <template #option="option">
                                    <div class="flex items-center gap-2">
                                        <Icon
                                            v-if="option.icon"
                                            :name="option.icon"
                                            class="size-4 shrink-0 text-orange-500 dark:text-orange-400"
                                        />
                                        <span>{{ option.label }}</span>
                                    </div>
                                </template>
                            </Combobox>
                        </li>
                    </ol>
                </div>
            </li>
        </ol>
    </div>
</template>
