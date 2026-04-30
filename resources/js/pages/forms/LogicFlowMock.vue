<script setup>
import { Combobox, Icon, Input } from '@ui';
import { ref } from 'vue';

const props = defineProps({
    initialConditionLabel: {
        type: String,
        default: null,
    },
    initialConditionIcon: {
        type: String,
        default: 'fieldtype-radio',
    },
    initialConditionIconClass: {
        type: String,
        default: 'bg-orange-50 text-orange-600 dark:bg-transparent dark:text-orange-400',
    },
    showSecondaryCondition: {
        type: Boolean,
        default: true,
    },
    mockPreset: {
        type: Object,
        default: () => ({}),
    },
    destinationStepLabel: {
        type: String,
        default: null,
    },
    showDestinationSelector: {
        type: Boolean,
        default: true,
    },
});

const logicOperator = ref(props.mockPreset.logicOperator || 'equals');
const logicOperatorOptions = [
    { label: __('Equals'), value: 'equals' },
    { label: __('Does not equal'), value: 'not_equals' },
    { label: __('Contains'), value: 'contains' },
    { label: __('Is empty'), value: 'is_empty' },
];

const logicValue = ref(props.mockPreset.logicValue || 'referral');
const logicValueOptions = [
    { label: __('Friend referral'), value: 'referral' },
    { label: __('Google search'), value: 'google' },
    { label: __('21'), value: '21' },
    { label: __('Days of Thunder'), value: 'days_of_thunder' },
    { label: __('Endless Summer'), value: 'endless_summer' },
    { label: __('Nocturnal'), value: 'nocturnal' },
    { label: __('Kids'), value: 'kids' },
];

const logicDestination = ref(props.mockPreset.logicDestination || 'fan_length');
const logicDestinationOptions = [
    { label: __('How long have you been…'), value: 'fan_length', icon: 'text-short', category: 'text' },
    { label: __('And second favorite album?'), value: 'second_favorite', icon: 'fieldtype-radio', category: 'choice' },
    { label: __('Sign up for email notifications'), value: 'email_notifications', icon: 'fieldtype-checkboxes', category: 'choice' },
    { label: __('I want a free drink voucher'), value: 'free_drink_voucher', icon: 'fieldtype-toggle', category: 'choice' },
];
const logicConditionField = ref(props.mockPreset.logicConditionField || 'long_answer');
const logicConditionFieldOptions = [
    { label: __('What do you like most about our band? '), value: 'long_answer', icon: 'text-long', category: 'text' },
    { label: __('How did you hear about us?'), value: 'heard_about_us', icon: 'fieldtype-select', category: 'choice' },
    { label: __('How long have you been…'), value: 'fan_length', icon: 'text-short', category: 'text' },
    { label: __('And second favorite album?'), value: 'second_favorite', icon: 'fieldtype-radio', category: 'choice' },
    { label: __('Sign up for email notifications'), value: 'email_notifications', icon: 'fieldtype-checkboxes', category: 'choice' },
    { label: __('How old are you?'), value: 'age', icon: 'number', category: 'text' },
];

const logicJoin = ref(props.mockPreset.logicJoin || 'and');
const logicJoinOptions = [
    { label: __('And'), value: 'and' },
    { label: __('Or'), value: 'or' },
];

const logicContainsOperator = ref(props.mockPreset.logicContainsOperator || 'contains');
const logicContainsAnswer = ref(props.mockPreset.logicContainsAnswer || 'referral');
const logicContainsAnswerPlaceholder = __('Answer');

const optionIconClasses = (option) => {
    if (option?.category === 'text') return 'size-4 shrink-0 text-purple-500 dark:text-purple-400';
    return 'size-4 shrink-0 text-orange-500 dark:text-orange-400';
};

const optionChipClasses = (option) => {
    if (option?.category === 'text') return 'flex shrink-0 items-center justify-center size-6 bg-purple-50 text-purple-600 dark:bg-transparent dark:text-purple-400 rounded-sm';
    return 'flex shrink-0 items-center justify-center size-6 bg-orange-50 text-orange-600 dark:bg-transparent dark:text-orange-400 rounded-sm';
};

const optionChipIconClasses = (option) => {
    if (option?.category === 'text') return 'size-4 shrink-0 rounded-sm text-purple-600 dark:text-purple-400';
    return 'size-4 shrink-0 rounded-sm text-orange-600 dark:text-orange-400';
};
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
                                class="logic-text__pill-icon size-5 rounded-sm"
                                :class="props.initialConditionIconClass"
                            >
                                <Icon :name="props.initialConditionIcon" class="size-3" />
                            </span>
                            <span class="logic-text__pill-text" :title="props.initialConditionLabel || __('Which album was your favorite?')">
                                {{ props.initialConditionLabel || __('Which album was your favorite?') }}
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
                    {{ props.destinationStepLabel || __('Then go to …') }}
                </div>
                <ol v-if="props.showDestinationSelector">
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
                                    <div class="flex shrink-0 items-center justify-center size-6 bg-orange-50 text-orange-600 dark:bg-transparent dark:text-orange-400 rounded-full">
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
                                class="logic-text__pill-icon size-6 rounded-full"
                                :class="props.initialConditionIconClass"
                            >
                                <Icon :name="props.initialConditionIcon" class="size-3.5" />
                            </span>
                            <span class="logic-text__pill-text" :title="props.initialConditionLabel || __('Which album was your favorite?')">
                                {{ props.initialConditionLabel || __('Which album was your favorite?') }}
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

            <li v-if="props.showSecondaryCondition">
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
                            v-model="logicConditionField"
                            size="sm"
                            variant="default"
                            :options="logicConditionFieldOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Destination')"
                            searchable
                        >
                            <template #option="{ icon, label, category }">
                                <div class="flex min-w-0 gap-2 items-center text-left">
                                    <Icon
                                        v-if="icon"
                                        :name="icon"
                                        :class="optionIconClasses({ category })"
                                    />
                                    <span class="block truncate">{{ label }}</span>
                                </div>
                            </template>
                            <template #selected-option="{ option }">
                                <div class="flex min-w-0 items-center gap-1 -ms-0.75">
                                    <div :class="optionChipClasses(option)">
                                        <Icon
                                            v-if="option.icon"
                                            :name="option.icon"
                                            :class="optionChipIconClasses(option)"
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
                    {{ props.destinationStepLabel || __('Then go to …') }}
                </div>
                <ol v-if="props.showDestinationSelector">
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
                            <template #option="{ icon, label, category }">
                                <div class="flex min-w-0 gap-2 items-center text-left">
                                    <Icon
                                        v-if="icon"
                                        :name="icon"
                                        :class="optionIconClasses({ category })"
                                    />
                                    <span class="block truncate">{{ label }}</span>
                                </div>
                            </template>
                            <template #selected-option="{ option }">
                                <div class="flex min-w-0 items-center gap-1 -ms-0.75">
                                    <div :class="optionChipClasses(option)">
                                        <Icon
                                            v-if="option.icon"
                                            :name="option.icon"
                                            :class="optionChipIconClasses(option)"
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
