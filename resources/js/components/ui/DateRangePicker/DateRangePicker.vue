<script setup>
import { config } from '@api';
import { computed } from 'vue';
import { normalizeLocale } from '../../FormattingLocale.js';
import {
    DateRangePickerCalendar,
    DateRangePickerCell,
    DateRangePickerCellTrigger,
    DateRangePickerGrid,
    DateRangePickerGridBody,
    DateRangePickerGridHead,
    DateRangePickerGridRow,
    DateRangePickerHeadCell,
    DateRangePickerHeader,
    DateRangePickerHeading,
    DateRangePickerNext,
    DateRangePickerPrev,
    DateRangePickerAnchor,
    DateRangePickerContent,
    DateRangePickerField,
    DateRangePickerInput,
    DateRangePickerRoot,
    DateRangePickerTrigger,
} from 'reka-ui';
import Card from '../Card/Card.vue';
import Button from '../Button/Button.vue';
import Calendar from '../Calendar/Calendar.vue';
import Icon from '../Icon/Icon.vue';
import Text from '../Text.vue';
import TimezoneHoverCard from '../TimezoneHoverCard.vue';
import { parseAbsoluteToLocal } from '@internationalized/date';
import { getAdditionalTimezones } from '../DatePicker/util.js';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    /** Badge text to display. */
    badge: { type: String, default: null },
    required: { type: Boolean, default: false },
    /** The controlled date range value. <br><br> Should be a [`DateRange` object](https://reka-ui.com/docs/guides/dates). */
    modelValue: { type: [Object, String], default: null },
    /** The minimum selectable date. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates). */
    min: { type: [String, Object], default: null },
    /** The maximum selectable date. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates). */
    max: { type: [String, Object], default: null },
    /** The granularity of the date range picker. <br><br> Options: `day`, `hour`, `minute`, `second` */
    granularity: { type: String, default: null },
    /** When `true`, the calendar is always visible instead of appearing in a popover. */
    inline: { type: Boolean, default: false },
    /** When `true`, a clear button is displayed to reset the date range. */
    clearable: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    readOnly: { type: Boolean, default: false },
});

const calendarBindings = computed(() => ({
    modelValue: props.modelValue ?? [],
    min: props.min,
    max: props.max,
    inline: props.inline,
    components: {
        Root: DateRangePickerCalendar,
        Header: DateRangePickerHeader,
        Heading: DateRangePickerHeading,
        Prev: DateRangePickerPrev,
        Next: DateRangePickerNext,
        Grid: DateRangePickerGrid,
        GridHead: DateRangePickerGridHead,
        GridBody: DateRangePickerGridBody,
        GridRow: DateRangePickerGridRow,
        HeadCell: DateRangePickerHeadCell,
        Cell: DateRangePickerCell,
        CellTrigger: DateRangePickerCellTrigger,
    },
}));

// Initial month when there is no value. Use defaultPlaceholder (not placeholder) so
// the range root stays uncontrolled: month navigation can update the visible month,
// and shared calendar UI (e.g. Today) can read that state from inject context.
// Still an absolute local ZonedDateTime so defaults match full date/time shape.
const defaultPlaceholder = parseAbsoluteToLocal(new Date().toISOString());

const calendarEvents = computed(() => ({
    'update:model-value': (event) => {
        if (props.granularity === 'day') {

            // Avoid fatal error `Cannot set properties of undefined (setting 'hour')`
            if (event.end == null) {
              return
            }

            event.start.hour = 0;
            event.start.minute = 0;
            event.start.second = 0;
            event.start.millisecond = 0;

            event.end.hour = 0;
            event.end.minute = 0;
            event.end.second = 0;
            event.end.millisecond = 0;
        }

        emit('update:modelValue', event)
    },
}));

const timeZoneName = computed(() => props.modelValue?.start?.timeZone ?? null);

const timeZoneLabel = computed(() => {
    const tz = timeZoneName.value;
    if (!tz) return null;

    const parts = new Intl.DateTimeFormat(normalizeLocale(config.get('translationLocale')), { timeZone: tz, timeZoneName: 'short' }).formatToParts(props.modelValue.start.toDate());
    return parts.find((p) => p.type === 'timeZoneName')?.value ?? tz;
});

const additionalTimezones = computed(() => getAdditionalTimezones(timeZoneName.value));

const hoverCardDate = computed(() => {
    if (!props.modelValue?.start || !props.modelValue?.end) return null;
    return { start: props.modelValue.start.toDate(), end: props.modelValue.end.toDate() };
});
</script>

<template>
    <div class="group/input relative block w-full" data-ui-input>
        <DateRangePickerRoot
            :modelValue="modelValue ?? { start: undefined, end: undefined }"
            :granularity="granularity"
            :locale="$date.locale"
            :disabled="disabled || readOnly"
            @update:model-value="emit('update:modelValue', $event)"
            v-bind="$attrs"
            prevent-deselect
            hide-time-zone
            :default-placeholder="defaultPlaceholder"
            close-on-select
        >
            <DateRangePickerField v-slot="{ segments }" class="w-full">
                <DateRangePickerAnchor as-child>
                <div
                    :class="[
                        'flex items-center w-full overflow-x-auto overflow-y-hidden bg-white dark:bg-gray-900',
                        'border border-gray-300 dark:border-gray-700',
                        'leading-[1.375rem] text-gray-600 dark:text-gray-300 @max-xs:text-xs',
                        'shadow-ui-sm not-prose h-10 rounded-lg px-2 disabled:shadow-none',
                        'data-invalid:border-red-500',
                        'disabled:shadow-none disabled:opacity-50',
                        readOnly ? 'border-dashed' : '',
                    ]"
                >
                    <DateRangePickerTrigger
                        v-if="!inline"
                        class="flex shrink-0 items-center justify-center rounded-lg p-2 -ms-1 text-gray-500 dark:text-gray-400 outline-hidden hover:bg-gray-100 focus:bg-gray-100 dark:hover:bg-gray-900 dark:focus:bg-gray-900"
                        :aria-label="__('Open calendar')"
                    >
                        <Icon name="calendar" class="size-4" />
                    </DateRangePickerTrigger>
                    <template v-for="item in segments.start" :key="item.part">
                        <DateRangePickerInput v-if="item.part === 'literal'" :part="item.part" type="start" class="whitespace-pre">
                            {{ item.value }}
                        </DateRangePickerInput>
                        <DateRangePickerInput
                            v-else
                            :part="item.part"
                            class="rounded-sm py-0.5 focus:bg-blue-100 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-blue-900 dark:data-placeholder:text-gray-400"
                            :class="{
                                'px-0.25!': item.part === 'month' || item.part === 'year' || item.part === 'day',
                            }"
                            type="start"
                        >
                            {{ item.value }}
                        </DateRangePickerInput>
                    </template>
                    <span class="mx-0.75 text-gray-400 dark:text-gray-600">&ndash;</span>
                    <template v-for="item in segments.end" :key="item.part">
                        <DateRangePickerInput v-if="item.part === 'literal'" :part="item.part" type="end" class="whitespace-pre">
                            {{ item.value }}
                        </DateRangePickerInput>
                        <DateRangePickerInput
                            v-else
                            :part="item.part"
                            class="rounded-sm py-0.5 focus:bg-blue-100 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-gray-800 dark:data-placeholder:text-gray-400"
                            :class="{
                                'px-0.25!': item.part === 'month' || item.part === 'year' || item.part === 'day',
                            }"
                            type="end"
                        >
                            {{ item.value }}
                        </DateRangePickerInput>
                    </template>
                    <div class="flex-1" />
                    <TimezoneHoverCard
                        v-if="timeZoneLabel && hoverCardDate"
                        :date="hoverCardDate"
                        :additional-timezones="additionalTimezones"
                        side="top"
                    >
                        <Text class="text-gray-600! dark:text-gray-400! ms-2" size="xs" :text="timeZoneLabel" />
                    </TimezoneHoverCard>
                    <Button v-if="!readOnly" @click="emit('update:modelValue', null)" variant="subtle" size="sm" icon="x" class="-me-2" :disabled="disabled" />
                </div>
                </DateRangePickerAnchor>
            </DateRangePickerField>

            <DateRangePickerContent
                v-if="!inline"
                align="start"
                :side-offset="4"
                class="data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade will-change-[transform,opacity]"
            >
                <Card class="w-[20rem]">
                    <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
                </Card>
            </DateRangePickerContent>

            <Card v-if="inline" class="mt-2">
                <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
            </Card>
        </DateRangePickerRoot>
    </div>
</template>
