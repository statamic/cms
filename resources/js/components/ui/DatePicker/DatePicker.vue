<script setup>
import { config } from '@api';
import { computed } from 'vue';
import { normalizeLocale } from '../../FormattingLocale.js';
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
import Text from '../Text.vue';
import TimezoneHoverCard from '../TimezoneHoverCard.vue';
import { getAdditionalTimezones } from './util.js';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    /** Badge text to display. */
    badge: { type: String, default: null },
    required: { type: Boolean, default: false },
    /** The controlled date value. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates) */
    modelValue: { type: [Object, String], default: null },
    /** The minimum selectable date. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates) */
    min: { type: [String, Object], default: null },
    /** The maximum selectable date. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates) */
    max: { type: [String, Object], default: null },
    /** The granularity of the date picker. <br><br> Options: `day`, `hour`, `minute`, `second` */
    granularity: { type: String, default: null },
    /** When `true`, the calendar is always visible instead of appearing in a popover. */
    inline: { type: Boolean, default: false },
    /** The number of months to display in the calendar. */
    numberOfMonths: { type: Number, default: 1 },
    /** When `true`, a clear button is displayed to reset the date. */
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

const timeZoneName = computed(() => props.modelValue?.timeZone ?? null);

const timeZoneLabel = computed(() => {
    const tz = timeZoneName.value;
    if (!tz) return null;

    const parts = new Intl.DateTimeFormat(normalizeLocale(config.get('translationLocale')), { timeZone: tz, timeZoneName: 'short' }).formatToParts(props.modelValue.toDate());
    return parts.find((p) => p.type === 'timeZoneName')?.value ?? tz;
});

const additionalTimezones = computed(() => getAdditionalTimezones(timeZoneName.value));

const isInvalid = computed(() => {
    // Check if the component has invalid state from form validation
    return props.modelValue === null && props.required;
});

const hasTime = computed(() => props.granularity != null && props.granularity !== 'day');

// A narrow date and time field with the clear × needs the smaller sizes to fit
const compactClear = computed(() => hasTime.value && props.clearable && !props.readOnly);

// Seconds or a timezone with minutes (GMT+5:30, GMT+13:45) make the value too long for the compact sizes, so they get an even
// smaller step
const longValue = computed(() => props.granularity === 'second' || (timeZoneLabel.value?.includes(':') ?? false));

// A date and time field sizes its text to its own width, so its root is a container. A container's content stops counting towards its
// width, so where the column is sized by its content (a Grid table), contain-intrinsic-inline-size stands in for a full-size value.

// Under 16rem the date and timezone shrink, and shrink a step further for a long value
// The longest value (seconds and a timezone with minutes) beside the clear × only fits with everything around it tightened too
const longestClear = computed(() => compactClear.value && props.granularity === 'second' && (timeZoneLabel.value?.includes(':') ?? false));

const compactDateClass = computed(() => {
    if (!hasTime.value) return null;
    return longValue.value ? '@max-[16rem]:text-[0.625rem]' : '@max-[16rem]:text-2xs';
});
const compactTimeZoneClass = computed(() => {
    if (!hasTime.value) return null;
    return longValue.value ? '@max-[16rem]:text-[0.5625rem]' : '@max-[16rem]:text-[0.625rem]';
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
    <div
        class="group/input relative block w-full"
        :class="{ '@container [contain-intrinsic-inline-size:18.5rem]': hasTime && !inline }"
        data-ui-input
    >
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
            close-on-select
        >
            <DatePickerField v-slot="{ segments }" class="w-full">
                <DatePickerAnchor as-child>
                    <div
                        :class="[
                            'flex w-full items-center overflow-x-auto overflow-y-hidden bg-white uppercase dark:bg-gray-900',
                            'border border-gray-300 dark:border-gray-700',
                            'leading-[1.375rem] text-gray-600 dark:text-gray-300',
                            'shadow-ui-sm not-prose h-10 rounded-lg px-2 disabled:shadow-none',
                            // Date and time only just fits a narrow field, so under 16rem the text shrinks and the gaps tighten
                            hasTime ? '@max-[16rem]:px-1.5' : '',
                            'data-invalid:border-red-500',
                            'disabled:shadow-none disabled:opacity-50',
                            readOnly ? 'border-dashed read-only-field:not-dark:bg-gray-100 read-only-field:text-gray-600 read-only-field:not-dark:shadow-none dark:read-only-field:text-gray-500 dark:read-only-field:**:text-gray-500' : '',
                        ]"
                        :aria-invalid="isInvalid"
                        role="textbox"
                        :aria-label="__('Select date')"
                    >
                        <DatePickerTrigger
                            v-if="!inline"
                            :class="{ '@max-[16rem]:ms-0 @max-[16rem]:p-1': hasTime && !longestClear, '@max-[16rem]:ms-0 @max-[16rem]:me-1 @max-[16rem]:p-0': longestClear }"
                            class="flex items-center justify-center rounded-lg cursor-pointer p-2 -ms-1 text-gray-500 dark:text-gray-400 outline-hidden hover:bg-gray-100 focus:bg-gray-100 dark:hover:bg-gray-900 dark:focus:bg-gray-900"
                            :aria-label="__('Open calendar')"
                        >
                            <Icon name="calendar" class="size-4" :class="{ '@max-[16rem]:size-3': hasTime && !longestClear, '@max-[16rem]:size-2.5': longestClear }" />
                        </DatePickerTrigger>
                        <div class="flex flex-1 items-center" :class="compactDateClass">
                            <template v-for="item in segments" :key="item.part">
                                <div v-if="item.part === 'literal'">
                                    <DatePickerInput
                                        :part="item.part"
                                        class="whitespace-pre"
                                        :class="{ 'text-gray-600 dark:text-gray-400': !item.contenteditable }"
                                        v-on="inputEvents"
                                    >
                                        {{ item.value }}
                                    </DatePickerInput>
                                </div>
                                <div v-else>
                                    <DatePickerInput
                                        :part="item.part"
                                        class="rounded-sm py-0.5 focus:bg-blue-100 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-blue-900 dark:data-placeholder:text-gray-400"
                                        :class="{
                                            'px-0.25!': item.part === 'month' || item.part === 'year' || item.part === 'day',
                                        }"
                                        :aria-label="getInputLabel(item.part)"
                                        v-on="inputEvents"
                                    >
                                        {{ item.value }}
                                    </DatePickerInput>
                                </div>
                            </template>
                        </div>
                        <TimezoneHoverCard
                            v-if="timeZoneLabel"
                            :date="modelValue.toDate()"
                            :additional-timezones="additionalTimezones"
                            :show-seconds="granularity === 'second'"
                            side="top"
                        >
                            <Text class="text-gray-600! dark:text-gray-400! ms-2" :class="[{ '@max-[16rem]:ms-1': hasTime && !longestClear, '@max-[16rem]:ms-0.5': longestClear, 'me-1': readOnly }, compactTimeZoneClass]" size="xs" :text="timeZoneLabel" />
                        </TimezoneHoverCard>
                        <Button
                            v-if="clearable && !readOnly"
                            @click="emit('update:modelValue', null)"
                            variant="subtle"
                            size="sm"
                            icon="x"
                            class="-my-1.25 -me-2"
                            :class="{
                                '@max-[16rem]:my-0 @max-[16rem]:-me-1 @max-[16rem]:[&_svg]:size-3': compactClear,
                                '@max-[16rem]:size-6.5': compactClear && !longestClear,
                                '@max-[16rem]:size-5.5': longestClear,
                            }"
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
