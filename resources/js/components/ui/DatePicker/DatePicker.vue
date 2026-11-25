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

// A date and time can be too long for a narrow field (25% and 33% columns), so it steps down by its own width (see the style block below).
// The steps are an approximation; when a long value (seconds, a 12-hour clock, a long timezone) still doesn't fit, the field scrolls, as
// it always has.
const fitsWidth = computed(() => hasTime.value && !props.inline);

// The root is a container, which stops its content counting towards its width. Where a column is sized by its content (a Grid table),
// this stands in for the width of the full-size value: 17.25rem with the clear ×, 16rem without, and 1.5rem more each for seconds and a
// timezone with minutes. Starting just above the 17rem compact step keeps a squeezed Grid column at a 14px date.
const intrinsicWidth = computed(() => {
    let rem = props.clearable && !props.readOnly ? 17.25 : 16;
    if (props.granularity === 'second') rem += 1.5;
    if (timeZoneLabel.value?.includes(':')) rem += 1.5;
    return `${rem}rem`;
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
        :style="fitsWidth ? { containIntrinsicInlineSize: intrinsicWidth } : null"
        :data-date-picker-fits-width="fitsWidth || null"
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
                            'data-invalid:border-red-500',
                            'disabled:shadow-none disabled:opacity-50',
                            readOnly ? 'border-dashed read-only-field:not-dark:bg-gray-100 read-only-field:text-gray-600 read-only-field:not-dark:shadow-none dark:read-only-field:text-gray-500 dark:read-only-field:**:text-gray-500' : '',
                        ]"
                        data-date-picker-box
                        :aria-invalid="isInvalid"
                        role="textbox"
                        :aria-label="__('Select date')"
                    >
                        <DatePickerTrigger
                            v-if="!inline"
                            data-date-picker-trigger
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg cursor-pointer p-2 -ms-1 text-gray-500 dark:text-gray-400 outline-hidden hover:bg-gray-100 focus:bg-gray-100 dark:hover:bg-gray-900 dark:focus:bg-gray-900"
                            :aria-label="__('Open calendar')"
                        >
                            <Icon name="calendar" class="size-4" />
                        </DatePickerTrigger>
                        <div class="flex flex-1 items-center" data-date-picker-date>
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
                            <Text class="text-gray-600! dark:text-gray-400! ms-2" :class="{ 'me-1': readOnly }" size="xs" :text="timeZoneLabel" data-date-picker-timezone />
                        </TimezoneHoverCard>
                        <Button
                            v-if="clearable && !readOnly"
                            @click="emit('update:modelValue', null)"
                            variant="subtle"
                            size="sm"
                            icon="x"
                            class="-my-1.25 -me-2"
                            data-date-picker-clear
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

<style>
    /* A date and time steps down as its field narrows: a 14px date under 20rem; then compact (13.2px date, 12px icon, 26px ×, tighter gaps)
       under 17rem; then, with the ×, tight (a 24px button with no padding round the icon, 22px ×) under 15rem. The date never goes below 13.2px or the icon
       below 12px; anything still too long scrolls. */
    [data-date-picker-fits-width] {
        container-type: inline-size;
    }

    @container (max-width: 20rem) {
        [data-date-picker-fits-width] [data-date-picker-date] {
            font-size: var(--text-sm);
        }
    }

    @container (max-width: 17rem) {
        [data-date-picker-fits-width] {
            [data-date-picker-box] {
                padding-inline: 0.375rem;
            }
            [data-date-picker-trigger] {
                position: relative;
                isolation: isolate;
                width: 1.25rem;
                height: 1.25rem;
                margin-inline-start: 0;
                padding: 0.25rem;
                background-color: transparent;

                /* The hover and focus background is a 16px square round the 12px icon, rather than filling the whole button */
                &::before {
                    content: '';
                    position: absolute;
                    z-index: -1;
                    inset: 0.125rem;
                    border-radius: 0.25rem;
                }
                &:is(:hover, :focus)::before {
                    background-color: var(--color-gray-100);
                }
                :root.dark &:is(:hover, :focus)::before {
                    background-color: var(--color-gray-900);
                }

                svg {
                    width: 0.75rem;
                    height: 0.75rem;
                }
            }
            [data-date-picker-date] {
                font-size: var(--text-xs);
            }
            [data-date-picker-timezone] {
                margin-inline-start: 0.25rem;
            }
            [data-date-picker-clear] {
                width: 1.625rem;
                height: 1.625rem;
                margin-block: 0;
                margin-inline-end: -0.25rem;

                svg {
                    width: 0.75rem;
                    height: 0.75rem;
                }
            }
        }
    }

    @container (max-width: 15rem) {
        [data-date-picker-fits-width]:has([data-date-picker-clear]) {
            /* A 24px target round the 12px icon, pulled back over the padding so the field gets no wider, and a ring when it's
               focused, since a background this small doesn't show */
            [data-date-picker-trigger] {
                width: 1.5rem;
                height: 1.5rem;
                padding: 0;
                margin-inline: -0.375rem -0.125rem;

                &::before {
                    inset: 0.25rem;
                }

                &:focus-visible {
                    outline: 2px solid var(--color-blue-500);
                    outline-offset: -2px;
                }
            }
            [data-date-picker-timezone] {
                margin-inline-start: 0.125rem;
            }
            [data-date-picker-clear] {
                width: 1.375rem;
                height: 1.375rem;
            }
        }
    }
</style>
