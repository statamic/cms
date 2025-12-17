<script setup>
import { computed } from 'vue';
import {
    DatePickerAnchor,
    DatePickerContent,
    DatePickerField,
    DatePickerInput,
    DatePickerRoot,
    DatePickerTrigger,
    DatePickerCalendar,
    DatePickerCell,
    DatePickerCellTrigger,
    DatePickerGrid,
    DatePickerGridBody,
    DatePickerGridHead,
    DatePickerGridRow,
    DatePickerHeadCell,
    DatePickerHeader,
    DatePickerHeading,
    DatePickerNext,
    DatePickerPrev,
} from 'reka-ui';
import Card from '../Card/Card.vue';
import Button from '../Button/Button.vue';
import Calendar from '../Calendar/Calendar.vue';
import Icon from '../Icon/Icon.vue';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    date: { type: String, default: null },
    badge: { type: String, default: null },
    required: { type: Boolean, default: false },
    modelValue: { type: [Object, String], default: null },
    min: { type: [String, Object], default: null },
    max: { type: [String, Object], default: null },
    granularity: { type: String, default: null },
    inline: { type: Boolean, default: false },
    numberOfMonths: { type: Number, default: 1 },
    clearable: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    readOnly: { type: Boolean, default: false },
});

const calendarBindings = computed(() => ({
    modelValue: props.modelValue,
    min: props.min,
    max: props.max,
    inline: props.inline,
    numberOfMonths: props.numberOfMonths,
    components: {
        Root: DatePickerCalendar,
        Header: DatePickerHeader,
        Heading: DatePickerHeading,
        Prev: DatePickerPrev,
        Next: DatePickerNext,
        Grid: DatePickerGrid,
        GridHead: DatePickerGridHead,
        GridBody: DatePickerGridBody,
        GridRow: DatePickerGridRow,
        HeadCell: DatePickerHeadCell,
        Cell: DatePickerCell,
        CellTrigger: DatePickerCellTrigger,
    },
}));

const inputEvents = computed(() => ({
	focusout: (event) => {
		if (props.modelValue?.year.toString().length === 2) {
			let value = props.modelValue;
			value.year = '20' + value.year;

			emit('update:modelValue', value);
		}
	},
}));

const calendarEvents = computed(() => ({
    'update:model-value': (event) => {
        if (props.granularity === 'day') {
            event.hour = 0;
            event.minute = 0;
            event.second = 0;
            event.millisecond = 0;
        }

        emit('update:modelValue', event);
    },
}));

const isInvalid = computed(() => {
    // Check if the component has invalid state from form validation
    return props.modelValue === null && props.required;
});

const getInputLabel = (part) => {
    switch (part) {
        case 'day':
            return __('Day');
        case 'month':
            return __('Month');
        case 'year':
            return __('Year');
        case 'hour':
            return __('Hour');
        case 'minute':
            return __('Minute');
        case 'second':
            return __('Second');
        case 'dayPeriod':
            return __('AM/PM');
        default:
            return '';
    }
};
</script>

<template>
    <div class="group/input relative block w-full" data-ui-input>
        <DatePickerRoot
            :modelValue="modelValue"
            :granularity="granularity"
            :locale="$date.locale"
            :disabled="disabled || readOnly"
            @update:model-value="emit('update:modelValue', $event)"
            v-bind="$attrs"
            prevent-deselect
            hide-time-zone
            role="group"
            :aria-label="__('Date picker')"
            :aria-required="required"
        >
            <DatePickerField v-slot="{ segments }" class="w-full">
                <DatePickerAnchor as-child>
                    <div
                        :class="[
                            'flex w-full items-center bg-white uppercase dark:bg-gray-900',
                            'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/10 dark:inset-shadow-2xs dark:inset-shadow-black',
                            'text-gray-600 dark:text-gray-300',
                            'shadow-ui-sm not-prose h-10 rounded-lg px-2 disabled:shadow-none',
                            'data-invalid:border-red-500',
                            'disabled:shadow-none disabled:opacity-50',
                            readOnly ? 'border-dashed' : '',
                        ]"
                        :aria-invalid="isInvalid"
                        role="textbox"
                        :aria-label="__('Select date')"
                    >
                        <DatePickerTrigger
                            v-if="!inline"
                            class="flex items-center justify-center rounded-lg p-2 -ms-1 text-gray-500 dark:text-gray-400 outline-hidden hover:bg-gray-100 focus:bg-gray-100 dark:hover:bg-gray-900 dark:focus:bg-gray-900"
                            :aria-label="__('Open calendar')"
                        >
                            <Icon name="calendar" class="size-4" />
                        </DatePickerTrigger>
                        <div class="flex items-center flex-1">
                            <template v-for="item in segments" :key="item.part">
	                            <div v-if="item.part === 'literal'">
	                                <DatePickerInput
	                                    :part="item.part"
	                                    :class="{ 'text-sm text-gray-600 dark:text-gray-400 antialiased': !item.contenteditable }"
	                                    v-on="inputEvents"
	                                >
	                                    {{ item.value }}
	                                </DatePickerInput>
	                            </div>
	                            <div v-else>
		                            <DatePickerInput
			                            :part="item.part"
			                            class="rounded-sm px-0.25 py-0.5 focus:bg-blue-100 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-blue-900 dark:data-placeholder:text-gray-400"
			                            :class="{
	                                        'px-0.5!': item.part === 'month' || item.part === 'year' || item.part === 'day',
	                                    }"
			                            :aria-label="getInputLabel(item.part)"
			                            v-on="inputEvents"
		                            >
			                            {{ item.value }}
		                            </DatePickerInput>
	                            </div>
                            </template>
                        </div>
                        <Button
                            v-if="clearable && !readOnly"
                            @click="emit('update:modelValue', null)"
                            variant="subtle"
                            size="sm"
                            icon="x"
                            class="-my-1.25 -me-2"
                            :disabled="disabled"
                            v-tooltip="__('Clear date')"
                        />
                    </div>
                </DatePickerAnchor>
            </DatePickerField>

            <DatePickerContent
                v-if="!inline"
                align="start"
                :side-offset="4"
                class="data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade will-change-[transform,opacity]"
            >
                <Card class="w-[20rem]">
                    <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
                </Card>
            </DatePickerContent>

            <Card v-if="inline" class="mt-2">
                <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
            </Card>
        </DatePickerRoot>
    </div>
</template>
