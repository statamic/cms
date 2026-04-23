<script setup>
import { Combobox, Icon, Input } from '@ui';
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
    { label: __('How long have you been…'), value: 'fan_length', icon: 'text-short' },
    { label: __('Sign up for email notifications'), value: 'email_notifications', icon: 'fieldtype-checkboxes' },
];

const logicJoin = ref('and');
const logicJoinOptions = [
    { label: __('And'), value: 'and' },
    { label: __('Or'), value: 'or' },
];

const logicContainsOperator = ref('contains');
const logicContainsAnswer = ref('5');
const logicContainsAnswerPlaceholder = __('Answer');
</script>

<template>
    <div data-logic-text class="logic-text">
        <h3 class="sr-only">{{ __('Conditional logic') }}</h3>

        <!-- Demo 1 -->
        <!-- <ol>
            <li>
                <div class="logic-text-badge logic-text__condition" aria-hidden="true">
                    {{ __('If') }}
                </div>
                <ol>
                    <li>
                        <div
                            class="logic-text__pill"
                        >
                            <span
                                class="logic-text__pill-icon size-6 bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400 rounded-full"
                            >
                                <Icon name="fieldtype-radio" class="size-3.5" />
                            </span>
                            <span class="logic-text__pill-text" :title="__('Which album was your favorite? Test for a long string and truncation')">
                                {{ __('Which album was your favorite? Test for a long string and truncation') }}
                            </span>
                        </div>
                    </li>
                    <li>
                        <Combobox
                            v-model="logicOperator"
                            size="sm"
                            :options="logicOperatorOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Operator')"
                            :searchable="false"
                        />
                    </li>
                    <li>
                        <Combobox
                            v-model="logicValue"
                            size="sm"
                            :options="logicValueOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Value')"
                            searchable
                        />
                    </li>
                </ol>
            </li>

            <li>
                <div class="logic-text-badge logic-text__condition" aria-hidden="true">
                    {{ __('Then go to …') }}
                </div>
                <ol>
                    <li>
                        <Combobox
                            v-model="logicDestination"
                            size="sm"
                            variant="default"
                            :options="logicDestinationOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Destination')"
                            searchable
                        >
                            <template #option="{ icon, label }">
                                <div class="flex gap-2 text-left">
                                    <Icon
                                        v-if="icon"
                                        :name="icon"
                                        class="size-4 shrink-0 text-orange-500 dark:text-orange-400"
                                    />
                                    <span class="truncate">{{ label }}</span>
                                </div>
                            </template>
                            <template #selected-option="{ option }">
                                <div class="flex items-center gap-2 -ms-0.75">
                                    <div class="flex shrink-0 items-center justify-center size-6 bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400 rounded-full">
                                        <Icon
                                            v-if="option.icon"
                                            :name="option.icon"
                                            class="size-4 shrink-0 rounded-full text-orange-600 dark:text-orange-400"
                                        />
                                    </div>
                                    <span>{{ option.label }}</span>
                                </div>
                            </template>
                        </Combobox>
                    </li>
                </ol>
            </li>
        </ol> -->

        <!-- Demo 2 -->
        <ol>
            <li>
                <div class="logic-text-badge logic-text__condition" aria-hidden="true">
                    {{ __('If') }}
                </div>
                <ol>
                    <li>
                        <div
                            class="logic-text__pill"
                        >
                            <span
                                class="logic-text__pill-icon size-6 bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400 rounded-full"
                            >
                                <Icon name="fieldtype-radio" class="size-3.5" />
                            </span>
                            <span class="logic-text__pill-text" :title="__('Which album was your favorite? Test for a long string and truncation')">
                                {{ __('Which album was your favorite? Test for a long string and truncation') }}
                            </span>
                        </div>
                    </li>
                    <li>
                        <Combobox
                            v-model="logicOperator"
                            size="sm"
                            :options="logicOperatorOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Operator')"
                            :searchable="false"
                        />
                    </li>
                    <li>
                        <Combobox
                            v-model="logicValue"
                            size="sm"
                            :options="logicValueOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Value')"
                            searchable
                        />
                    </li>
                </ol>
            </li>

            <li>
                <div class="logic-text__condition" aria-hidden="true">
                    <Combobox
                        v-model="logicJoin"
                        class="max-w-24"
                        size="sm"
                        :options="logicJoinOptions"
                        option-label="label"
                        option-value="value"
                        :placeholder="__('And')"
                        :searchable="false"
                    />
                </div>
                <ol>
                    <li>
                        <Combobox
                            v-model="logicDestination"
                            size="sm"
                            variant="default"
                            :options="logicDestinationOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Destination')"
                            searchable
                        >
                            <template #option="{ icon, label }">
                                <div class="flex min-w-0 gap-2 text-left">
                                    <Icon
                                        v-if="icon"
                                        :name="icon"
                                        class="size-4 shrink-0 text-purple-500 dark:text-purple-400"
                                    />
                                    <span class="block truncate">{{ label }}</span>
                                </div>
                            </template>
                            <template #selected-option="{ option }">
                                <div class="flex min-w-0 items-center gap-2 -ms-0.75">
                                    <div class="flex shrink-0 items-center justify-center size-6 bg-purple-50 text-purple-600 dark:bg-purple-500/25 dark:text-purple-400 rounded-full">
                                        <Icon
                                            v-if="option.icon"
                                            :name="option.icon"
                                            class="size-4 shrink-0 rounded-full text-purple-600 dark:text-purple-400"
                                        />
                                    </div>
                                    <span class="block truncate">{{ option.label }}</span>
                                </div>
                            </template>
                        </Combobox>
                    </li>
                    <li>
                        <Combobox
                            v-model="logicContainsOperator"
                            size="sm"
                            :options="logicOperatorOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Operator')"
                            :searchable="false"
                        />
                    </li>
                    <li>
                        <Input
                            v-model="logicContainsAnswer"
                            size="sm"
                            :input-attrs="{
                                size: logicContainsAnswer.length === 0
                                    ? logicContainsAnswerPlaceholder.length
                                    : logicContainsAnswer.length,
                                style: logicContainsAnswer.length === 0
                                    ? `min-width: ${logicContainsAnswerPlaceholder.length}ch;`
                                    : undefined,
                            }"
                            :placeholder="logicContainsAnswerPlaceholder"
                        />
                    </li>
                </ol>
            </li>

            <li>
                <div class="logic-text-badge logic-text__condition" aria-hidden="true">
                    {{ __('Then go to …') }}
                </div>
                <ol>
                    <li>
                        <Combobox
                            v-model="logicDestination"
                            size="sm"
                            variant="default"
                            :options="logicDestinationOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Destination')"
                            searchable
                        >
                            <template #option="{ icon, label }">
                                <div class="flex min-w-0 gap-2 text-left">
                                    <Icon
                                        v-if="icon"
                                        :name="icon"
                                        class="size-4 shrink-0 text-orange-500 dark:text-orange-400"
                                    />
                                    <span class="block truncate">{{ label }}</span>
                                </div>
                            </template>
                            <template #selected-option="{ option }">
                                <div class="flex min-w-0 items-center gap-2 -ms-0.75">
                                    <div class="flex shrink-0 items-center justify-center size-6 bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400 rounded-full">
                                        <Icon
                                            v-if="option.icon"
                                            :name="option.icon"
                                            class="size-4 shrink-0 rounded-full text-orange-600 dark:text-orange-400"
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
    </div>
</template>
