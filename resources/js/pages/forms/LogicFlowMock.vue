<script setup>
import { Button, Combobox, Dropdown, DropdownItem, DropdownLabel, DropdownMenu, DropdownSeparator, Icon, Input } from '@ui';
import { computed, ref, watch } from 'vue';

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
        default: 'text-orange-600 dark:text-orange-400',
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
    useWhenSelector: {
        type: Boolean,
        default: false,
    },
    calculationMode: {
        type: Boolean,
        default: false,
    },
    showRuleControls: {
        type: Boolean,
        default: false,
    },
    showAddConditionBeforeThen: {
        type: Boolean,
        default: false,
    },
    usePageDestinationOptions: {
        type: Boolean,
        default: false,
    },
});

const logicOperator = ref(props.mockPreset.logicOperator || (props.calculationMode ? 'add' : 'equals'));
const logicWhen = ref(props.mockPreset.logicWhen || (props.calculationMode ? 'add' : 'show_when'));
const logicWhenOptions = props.calculationMode
    ? [
        { label: `+ ${__('Add')}`, value: 'add' },
        { label: `÷ ${__('Divide')}`, value: 'divide' },
        { label: `− ${__('Subtract')}`, value: 'subtract' },
        { label: `× ${__('Multiply')}`, value: 'multiply' },
    ]
    : [
        { label: __('Always show'), value: 'always_show', icon: 'eye' },
        { label: __('Show when'), value: 'show_when', icon: 'eye' },
        { label: __('Hide when'), value: 'hide_when', icon: 'eye-closed' },
    ];
const logicOperatorOptions = props.calculationMode
    ? [
        { label: `+ ${__('Add')}`, value: 'add' },
        { label: `÷ ${__('Divide')}`, value: 'divide' },
        { label: `− ${__('Subtract')}`, value: 'subtract' },
        { label: `× ${__('Multiply')}`, value: 'multiply' },
    ]
    : [
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

const logicDestination = ref(props.mockPreset.logicDestination || 'page_2');
const logicBranchingAction = ref(props.mockPreset.logicBranchingAction || 'go_to');
const logicBranchingActionOptions = [
    { label: __('Go to'), value: 'go_to' },
    { label: ['+', __('Add')].join('\u2007'), value: 'add' },
    { label: ['÷', __('Divide')].join('\u2007'), value: 'divide' },
    { label: ['−', __('Subtract')].join('\u2007'), value: 'subtract' },
    { label: ['×', __('Multiply')].join('\u2007'), value: 'multiply' },
];
const fieldDestinationOptions = [
    { label: __('How long have you been…'), value: 'fan_length', icon: 'text-short', category: 'text' },
    { label: __('And second favorite album?'), value: 'second_favorite', icon: 'fieldtype-radio', category: 'choice' },
    { label: __('Sign up for email notifications'), value: 'email_notifications', icon: 'fieldtype-checkboxes', category: 'choice' },
    { label: __('I want a free drink voucher'), value: 'free_drink_voucher', icon: 'fieldtype-toggle', category: 'choice' },
];
const pageDestinationOptions = [
    { label: __('page 1/2'), value: 'page_1', icon: 'page', category: 'page' },
    { label: __('Goodbye'), value: 'page_2', icon: 'page', category: 'page' },
];
const logicConditionField = ref(props.mockPreset.logicConditionField || 'long_answer');
const logicPrimaryConditionField = ref(props.mockPreset.logicPrimaryConditionField || 'heard_about_us');
const logicBranchingConditionField = ref(props.mockPreset.logicBranchingConditionField || 'second_favorite');
const logicCalculationSource = ref(props.mockPreset.logicCalculationSource || 'variable_score');
const logicCalculationVariable = ref(props.mockPreset.logicCalculationVariable || 'bonus_multiplier');
const logicConditionFieldOptions = [
    { label: __('What do you like most about our band? '), value: 'long_answer', icon: 'text-long', category: 'text' },
    { label: __('How did you hear about us?'), value: 'heard_about_us', icon: 'fieldtype-select', category: 'choice' },
    { label: __('How long have you been…'), value: 'fan_length', icon: 'text-short', category: 'text' },
    { label: __('And second favorite album?'), value: 'second_favorite', icon: 'fieldtype-radio', category: 'choice' },
    { label: __('Sign up for email notifications'), value: 'email_notifications', icon: 'fieldtype-checkboxes', category: 'choice' },
    { label: __('How old are you?'), value: 'age', icon: 'number', category: 'text' },
];
const logicCalculationSourceOptions = [
    { label: __('Score'), value: 'variable_score' },
    { label: __('Number'), value: 'number' },
];
const logicCalculationVariableOptions = [
    { label: __('bonus_multiplier'), value: 'bonus_multiplier' },
    { label: __('base_score'), value: 'base_score' },
    { label: __('attendance_points'), value: 'attendance_points' },
    { label: __('engagement_weight'), value: 'engagement_weight' },
];
const logicBranchingCalculationSource = ref(props.mockPreset.logicBranchingCalculationSource || 'variable_score');
const logicBranchingCalculationVariable = ref(props.mockPreset.logicBranchingCalculationVariable || 'bonus_multiplier');

const logicJoin = ref(props.mockPreset.logicJoin || 'and');
const logicJoinOptions = [
    { label: __('All of the conditions pass'), short_label: __('And'), value: 'and' },
    { label: __('Any of the conditions pass'), short_label: __('Or'), value: 'or' },
];

const logicContainsOperator = ref(props.mockPreset.logicContainsOperator || 'contains');
const logicContainsAnswer = ref(props.mockPreset.logicContainsAnswer || 'referral');
const logicContainsAnswerPlaceholder = __('Answer');
const secondaryConditionVisible = ref(props.showSecondaryCondition);

const optionIconClasses = (option) => {
    if (option?.category === 'page') return 'size-4 shrink-0 text-gray-500 dark:text-gray-300';
    if (option?.category === 'text') return 'size-4 shrink-0 text-purple-500 dark:text-purple-400';
    return 'size-4 shrink-0 text-orange-500 dark:text-orange-400';
};

const optionChipClasses = (option) => {
    if (option?.category === 'page') return 'flex shrink-0 items-center justify-center size-6 text-gray-500 dark:text-gray-300';
    if (option?.category === 'text') return 'flex shrink-0 items-center justify-center size-6 text-purple-600 dark:text-purple-400';
    return 'flex shrink-0 items-center justify-center size-6 text-orange-600 dark:text-orange-400';
};

const optionChipIconClasses = (option) => {
    if (option?.category === 'page') return 'size-4 shrink-0 text-gray-500 dark:text-gray-300';
    if (option?.category === 'text') return 'size-4 shrink-0 text-purple-600 dark:text-purple-400';
    return 'size-4 shrink-0 text-orange-600 dark:text-orange-400';
};
const isVariableOption = (option) => option?.value === 'variable_score';

const primarySelection = computed({
    get() {
        return props.calculationMode ? logicCalculationSource.value : logicPrimaryConditionField.value;
    },
    set(value) {
        if (props.calculationMode) {
            logicCalculationSource.value = value;
            return;
        }

        logicPrimaryConditionField.value = value;
    },
});
const calculationUsesNumberInput = computed(() => props.calculationMode && primarySelection.value === 'number');
const branchingActionLabel = computed(() => {
    const selected = logicBranchingActionOptions.find((option) => option.value === logicBranchingAction.value);
    return selected?.label || __('Logic');
});
const isBranchingCalculationAction = computed(() => logicBranchingAction.value !== 'go_to');
const branchingCalculationUsesNumberInput = computed(() => logicBranchingCalculationSource.value === 'number');
const logicDestinationOptions = computed(() => (
    props.usePageDestinationOptions ? pageDestinationOptions : fieldDestinationOptions
));

watch(
    () => props.mockPreset.logicDestination,
    (nextDestination) => {
        if (!nextDestination) return;
        logicDestination.value = nextDestination;
    },
    { immediate: true },
);

watch(
    logicDestinationOptions,
    (options) => {
        if (!options.length) return;
        const hasCurrent = options.some((option) => option.value === logicDestination.value);
        if (!hasCurrent) {
            logicDestination.value = options[0].value;
        }
    },
    { immediate: true },
);
</script>

<template>
    <div data-logic-text class="logic-text">
        <h3 class="sr-only">{{ __('Conditional logic') }}</h3>

        <ol>
            <li>
                <div v-if="props.useWhenSelector" class="logic-text__condition" aria-hidden="true">
                    <Combobox
                        v-model="logicWhen"
                        class="min-w-34"
                        size="sm"
                        :options="logicWhenOptions"
                        option-label="label"
                        option-value="value"
                        :searchable="false"
                    >
                        <template v-if="!props.calculationMode" #option="{ icon, label }">
                            <span class="inline-flex items-center gap-2">
                                <Icon v-if="icon" :name="icon" class="size-3.5 text-gray-700 dark:text-gray-200" />
                                <span>{{ label }}</span>
                            </span>
                        </template>
                        <template v-if="!props.calculationMode" #selected-option="{ option }">
                            <span class="inline-flex items-center gap-2">
                                <Icon v-if="option.icon" :name="option.icon" class="size-3.5 text-gray-700 dark:text-gray-200" />
                                <span>{{ option.label }}</span>
                            </span>
                        </template>
                    </Combobox>
                </div>
                <div v-else class="flex items-center">
                    <div class="logic-text-badge logic-text__condition" aria-hidden="true">
                        {{ __('If') }}
                    </div>
                    <div
                        v-if="props.showRuleControls"
                        class="ms-0.5 mb-2.5 inline-flex items-center gap-1.5 opacity-0 pointer-events-none transition-opacity group-hover/logic-tab:opacity-100 group-hover/logic-tab:pointer-events-auto [@media(any-hover:none)]:opacity-100 [@media(any-hover:none)]:pointer-events-auto"
                    >
                        <Button size="sm" inset variant="subtle" class="size-7 [&_svg]:opacity-50 rounded-full ms-[0.025rem]" icon="trash" />
                        <!-- class="mb-2.5 mt-[0.5px] p-2.5 size-6 ms-0.25 rounded-full [&_div]:-translate-y-[1px] opacity-0 pointer-events-none transition-opacity group-hover/logic-tab:opacity-85 group-hover/logic-tab:pointer-events-auto [@media(any-hover:none)]:opacity-100 [@media(any-hover:none)]:pointer-events-auto" -->
                    </div>
                </div>
                <ol>
                    <li>
                        <Combobox
                            v-if="props.useWhenSelector"
                            v-model="primarySelection"
                            size="sm"
                            variant="default"
                            :options="props.calculationMode ? logicCalculationSourceOptions : logicConditionFieldOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="props.calculationMode ? __('Variable or number') : __('Field')"
                            :searchable="!props.calculationMode"
                        >
                            <template v-if="props.calculationMode" #option="{ label, value }">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span
                                        v-if="isVariableOption({ value })"
                                        class="inline-flex items-center rounded-md bg-violet-50 px-1.5 py-0.5 text-2xs font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300"
                                    >
                                        {{ __('Variable') }}
                                    </span>
                                    <span class="truncate">{{ label }}</span>
                                </div>
                            </template>
                            <template v-if="props.calculationMode" #selected-option="{ option }">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span
                                        v-if="isVariableOption(option)"
                                        class="inline-flex items-center rounded-md bg-violet-50 px-1.5 py-0.5 text-2xs font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300"
                                    >
                                        {{ __('Variable') }}
                                    </span>
                                    <span class="truncate" :class="{ 'font-mono lowercase text-xs tracking-tight': isVariableOption(option) }">{{ option.label }}</span>
                                </div>
                            </template>
                            <template v-if="!props.calculationMode" #option="{ icon, label, category }">
                                <div class="flex min-w-0 gap-2 items-center text-left">
                                    <Icon
                                        v-if="icon"
                                        :name="icon"
                                        :class="optionIconClasses({ category })"
                                    />
                                    <span class="block truncate">{{ label }}</span>
                                </div>
                            </template>
                            <template v-if="!props.calculationMode" #selected-option="{ option }">
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
                        <Combobox
                            v-else
                            v-model="logicBranchingConditionField"
                            size="sm"
                            variant="default"
                            :options="logicConditionFieldOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Field')"
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
                    <li v-if="props.calculationMode">
                        <div class="logic-text-badge logic-text__condition mb-0.25! ms-1.5" aria-hidden="true">
                            {{ __('to') }}
                        </div>
                    </li>
                    <li>
                        <Input
                            v-if="props.calculationMode && calculationUsesNumberInput"
                            v-model="logicValue"
                            size="sm"
                            :placeholder="__('Enter a number')"
                        />
                        <Combobox
                            v-else-if="props.calculationMode"
                            v-model="logicCalculationVariable"
                            size="sm"
                            :options="logicCalculationVariableOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Variable')"
                            :searchable="false"
                            class="font-mono lowercase [&_span]:text-xs tracking-tight"
                        />
                        <Combobox
                            v-else
                            v-model="logicOperator"
                            size="sm"
                            :options="logicOperatorOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Operator')"
                            :searchable="false"
                        />
                    </li>
                    <li v-if="!props.calculationMode">
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

            <li v-if="secondaryConditionVisible && !props.calculationMode">
                <div class="flex items-center gap-0.25">
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
                            adaptive-width
                        >
                            <template #selected-option="{ option }">
                                <span class="block truncate" v-text="option.short_label" />
                            </template>
                        </Combobox>
                    </div>
                    <Button
                        size="sm"
                        inset
                        variant="subtle"
                        class="mb-2.5 mt-[0.5px] p-2.5 size-6 ms-0.25 rounded-full [&_div]:-translate-y-[1px] opacity-0 pointer-events-none transition-opacity group-hover/logic-tab:opacity-85 group-hover/logic-tab:pointer-events-auto [@media(any-hover:none)]:opacity-100 [@media(any-hover:none)]:pointer-events-auto"
                        :text="'×'"
                        :aria-label="__('Remove condition')"
                        @click="secondaryConditionVisible = false"
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

            <li v-if="!props.useWhenSelector">
                <div class="logic-text-badge logic-text__condition" aria-hidden="true">
                    {{ __('Then') }}
                </div>
                <ol v-if="props.showDestinationSelector">
                    <li>
                        <Dropdown>
                            <template #trigger>
                                <button
                                    type="button"
                                    class="w-full min-w-0 flex items-center justify-between rounded-lg border border-gray-300 bg-linear-to-b from-white to-gray-50 px-3 h-8 text-sm text-gray-900 shadow-ui-sm dark:from-gray-850 dark:to-gray-900 dark:border-gray-700 dark:text-gray-300 dark:shadow-ui-md"
                                >
                                    <span class="truncate">{{ branchingActionLabel }}</span>
                                    <Icon name="chevron-down" class="ms-2 size-4 text-gray-400 dark:text-white/40" />
                                </button>
                            </template>
                            <DropdownMenu>
                                <DropdownLabel :text="__('Logic')" />
                                <DropdownItem :text="__('Go to')" @click="logicBranchingAction = 'go_to'" />
                                <DropdownSeparator />
                                <DropdownLabel :text="__('Calculation')" />
                                <DropdownItem @click="logicBranchingAction = 'add'">
                                    <span class="inline-flex items-center gap-2">
                                        <span>+</span>
                                        <span>{{ __('Add') }}</span>
                                    </span>
                                </DropdownItem>
                                <DropdownItem @click="logicBranchingAction = 'divide'">
                                    <span class="inline-flex items-center gap-2">
                                        <span>÷</span>
                                        <span>{{ __('Divide') }}</span>
                                    </span>
                                </DropdownItem>
                                <DropdownItem @click="logicBranchingAction = 'subtract'">
                                    <span class="inline-flex items-center gap-2">
                                        <span>−</span>
                                        <span>{{ __('Subtract') }}</span>
                                    </span>
                                </DropdownItem>
                                <DropdownItem @click="logicBranchingAction = 'multiply'">
                                    <span class="inline-flex items-center gap-2">
                                        <span>×</span>
                                        <span>{{ __('Multiply') }}</span>
                                    </span>
                                </DropdownItem>
                            </DropdownMenu>
                        </Dropdown>
                    </li>
                    <li v-if="logicBranchingAction === 'go_to'">
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
                    <li v-if="isBranchingCalculationAction">
                        <Combobox
                            v-model="logicBranchingCalculationSource"
                            size="sm"
                            variant="default"
                            :options="logicCalculationSourceOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Variable or number')"
                            :searchable="false"
                        >
                            <template #option="{ label, value }">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span
                                        v-if="isVariableOption({ value })"
                                        class="inline-flex items-center rounded-md bg-violet-50 px-1.5 py-0.5 text-2xs font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300"
                                    >
                                        {{ __('Variable') }}
                                    </span>
                                    <span class="truncate">{{ label }}</span>
                                </div>
                            </template>
                            <template #selected-option="{ option }">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span
                                        v-if="isVariableOption(option)"
                                        class="inline-flex items-center rounded-md bg-violet-50 px-1.5 py-0.5 text-2xs font-medium text-violet-700 dark:bg-violet-900/30 dark:text-violet-300"
                                    >
                                        {{ __('Variable') }}
                                    </span>
                                    <span class="truncate" :class="{ 'font-mono lowercase text-xs tracking-tight': isVariableOption(option) }">{{ option.label }}</span>
                                </div>
                            </template>
                        </Combobox>
                    </li>
                    <li v-if="isBranchingCalculationAction">
                        <div class="logic-text-badge logic-text__condition" aria-hidden="true">
                            {{ __('to') }}
                        </div>
                    </li>
                    <li v-if="isBranchingCalculationAction">
                        <Input
                            v-if="branchingCalculationUsesNumberInput"
                            v-model="logicValue"
                            size="sm"
                            :placeholder="__('Enter a number')"
                        />
                        <Combobox
                            v-else
                            v-model="logicBranchingCalculationVariable"
                            size="sm"
                            class="font-mono lowercase text-xs tracking-tight"
                            :options="logicCalculationVariableOptions"
                            option-label="label"
                            option-value="value"
                            :placeholder="__('Variable')"
                            :searchable="false"
                        >
                            <template #option="{ label }">
                                <span class="font-mono text-xs tracking-tight">{{ label }}</span>
                            </template>
                            <template #selected-option="{ option }">
                                <span class="font-mono text-xs tracking-tight">{{ option.label }}</span>
                            </template>
                        </Combobox>
                    </li>
                </ol>
            </li>
        </ol>

        <Button
            v-if="!props.useWhenSelector && props.showAddConditionBeforeThen"
            size="sm"
            variant="subtle"
            class="ms-4 bg-transparent!"
            :text="__('+ Add Condition')"
        />
    </div>
</template>
