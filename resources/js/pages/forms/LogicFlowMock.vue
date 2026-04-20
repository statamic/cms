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
    <div data-logic-text class="logic-text">
        <h3 class="sr-only">{{ __('Conditional logic') }}</h3>

        <ol>
            <li>
                <div class="logic-text__badge-wrapper">
                    <div class="logic-text__badge" aria-hidden="true">
                        {{ __('If') }}
                    </div>
                </div>
                <ol>
                    <li>
                        <div
                            class="logic-text__pill"
                        >
                            <span
                                class="logic-text__pill-icon"
                            >
                                <Icon name="fieldtype-radio" class="size-3.5" />
                            </span>
                            <span class="logic-text__pill-text">
                                {{ __('Which album was your favorite testing?') }}
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
                <div class="logic-text__badge-wrapper">
                    <div class="logic-text__badge" aria-hidden="true">
                        {{ __('Then go to …') }}
                    </div>
                </div>
                <ol>
                    <li>
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
            </li>
        </ol>
    </div>
</template>
