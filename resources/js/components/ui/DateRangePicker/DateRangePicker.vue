<script setup>
import { config } from '@api';
import { computed, nextTick, onUnmounted, ref, watch } from 'vue';
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
import { getLocalTimeZone, now, toCalendarDate } from '@internationalized/date';
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

/** Synced with DateRangePickerRoot so we can re-open after "Today" despite close-on-select. */
const pickerOpen = ref(false);

/** After "Today" shortcut, label shows "Select" until the range changes or the popover closes. */
const shortcutPrimedForSelect = ref(false);

/**
 * Reka closes the popover when start+end are both set (`close-on-select`). Our "Today" emit does that,
 * so the popover blips closed and `watch(pickerOpen)` would clear `shortcutPrimedForSelect` before
 * we re-open in `nextTick`. Skip clearing for that one automatic close.
 */
const ignoreNextPickerCloseForShortcut = ref(false);

/**
 * After "Select", keep start+end as the same calendar day (today) but set Reka `fixed-date="start"`.
 * Reka's changeDate() only updates `end` on the next cell click when *both* endpoints exist; if `end`
 * is cleared, a different code path runs that requires `highlightedRange` and often never completes.
 * (reka-ui: `RangeCalendarCellTrigger.vue` → `changeDate`.)
 */
const fixRangeStartForNextPick = ref(false);

const rangePickerFixedDate = computed(() => (fixRangeStartForNextPick.value ? 'start' : undefined));

const calendarEvents = computed(() => ({
    'update:model-value': (event) => {
        if (props.granularity === 'day') {

            // Avoid fatal error `Cannot set properties of undefined (setting 'hour')`
            if (event.end == null) {
                // Range mid-selection: parent v-model is not updated yet, so clear the
                // "Select" primed state here or the label would stay wrong.
                if (shortcutPrimedForSelect.value) {
                    shortcutPrimedForSelect.value = false;
                }
                if (fixRangeStartForNextPick.value) {
                    fixRangeStartForNextPick.value = false;
                }
                return;
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

const emitTodayValue = () => {
    fixRangeStartForNextPick.value = false;

    const tz = getLocalTimeZone();
    let start = now(tz).set({ millisecond: 0 });
    let end = now(tz).set({ millisecond: 0 });
    if (props.granularity === 'day') {
        start = start.set({ hour: 0, minute: 0, second: 0, millisecond: 0 });
        end = end.set({ hour: 0, minute: 0, second: 0, millisecond: 0 });
    }

    if (!props.inline) {
        ignoreNextPickerCloseForShortcut.value = true;
    }

    emit('update:modelValue', { start, end });
    shortcutPrimedForSelect.value = true;

    if (!props.inline) {
        nextTick(() => {
            pickerOpen.value = true;
            nextTick(() => {
                ignoreNextPickerCloseForShortcut.value = false;
            });
        });
    }
};

const isTodayRangeSelected = computed(() => {
    const mv = props.modelValue;
    if (!mv?.start || !mv?.end) {
        return false;
    }
    try {
        const todayCal = toCalendarDate(now(getLocalTimeZone()));
        const startCal = toCalendarDate(mv.start);
        const endCal = toCalendarDate(mv.end);
        const singleDay =
            startCal.year === endCal.year &&
            startCal.month === endCal.month &&
            startCal.day === endCal.day;
        const isToday =
            startCal.year === todayCal.year &&
            startCal.month === todayCal.month &&
            startCal.day === todayCal.day;
        return singleDay && isToday;
    } catch {
        return false;
    }
});

const todayShortcutLabel = computed(() =>
    shortcutPrimedForSelect.value && isTodayRangeSelected.value ? __('Select') : __('Today'),
);

watch(
    () => props.modelValue,
    () => {
        if (shortcutPrimedForSelect.value && !isTodayRangeSelected.value) {
            shortcutPrimedForSelect.value = false;
        }
        if (fixRangeStartForNextPick.value && !isTodayRangeSelected.value) {
            fixRangeStartForNextPick.value = false;
        }
    },
    { deep: true },
);

watch(pickerOpen, (open) => {
    if (!props.inline && !open) {
        if (ignoreNextPickerCloseForShortcut.value) {
            return;
        }
        shortcutPrimedForSelect.value = false;
        fixRangeStartForNextPick.value = false;
    }
});

onUnmounted(() => {
    shortcutPrimedForSelect.value = false;
    fixRangeStartForNextPick.value = false;
    ignoreNextPickerCloseForShortcut.value = false;
});

const onTodayShortcutClick = () => {
    if (props.disabled || props.readOnly) {
        return;
    }
    /** Same calendar day for start+end, but `fixed-date="start"` so the next click only moves `end`. */
    if (shortcutPrimedForSelect.value && isTodayRangeSelected.value) {
        fixRangeStartForNextPick.value = true;
        shortcutPrimedForSelect.value = false;
        if (!props.inline) {
            nextTick(() => {
                pickerOpen.value = true;
            });
        }
        return;
    }
    /** Always apply today (clears fixed-date / in-progress calendar state). Same as first "Today". */
    emitTodayValue();
};

const timeZoneName = computed(() => props.modelValue?.start?.timeZone ?? null);

const timeZoneLabel = computed(() => {
    const tz = timeZoneName.value;
    if (!tz) return null;

    const parts = new Intl.DateTimeFormat(config.get('translationLocale'), { timeZone: tz, timeZoneName: 'short' }).formatToParts(props.modelValue.start.toDate());
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
            close-on-select
            role="group"
            :aria-label="__('Date range picker')"
            :aria-required="required"
            v-model:open="pickerOpen"
            :fixed-date="rangePickerFixedDate"
        >
            <DateRangePickerField v-slot="{ segments }" class="w-full">
                <div
                    :class="[
                        'flex items-center w-full bg-white dark:bg-gray-900',
                        'border border-gray-300 dark:border-gray-700',
                        'leading-[1.375rem] text-gray-600 dark:text-gray-300',
                        'shadow-ui-sm not-prose h-10 rounded-lg py-2 px-2.5 disabled:shadow-none',
                        'data-invalid:border-red-500',
                        'disabled:shadow-none disabled:opacity-50',
                        readOnly ? 'border-dashed' : '',
                    ]"
                >
                    <DateRangePickerTrigger v-if="!inline">
                        <Button as="div" variant="ghost" size="sm" icon="calendar" class="-ms-2" />
                    </DateRangePickerTrigger>
                    <template v-for="item in segments.start" :key="item.part">
                        <DateRangePickerInput v-if="item.part === 'literal'" :part="item.part" type="start">
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
                        <DateRangePickerInput v-if="item.part === 'literal'" :part="item.part" type="end">
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
                        <Text class="text-gray-600 dark:text-gray-400 me-1" size="xs" :text="timeZoneLabel" />
                    </TimezoneHoverCard>
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
            </DateRangePickerField>

            <DateRangePickerContent
                v-if="!inline"
                align="start"
                :align-offset="-12"
                :side-offset="14"
                class="data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade will-change-[transform,opacity]"
            >
                <Card class="w-[20rem]">
                    <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
                    <div
                        class="flex justify-end"
                    >
                        <Button
                            type="button"
                            variant="subtle"
                            size="2xs"
                            class="me-1"
                            :text="todayShortcutLabel"
                            :disabled="disabled || readOnly"
                            @click="onTodayShortcutClick"
                        />
                    </div>
                </Card>
            </DateRangePickerContent>

            <Card v-if="inline" class="mt-2">
                <Calendar v-bind="calendarBindings" v-on="calendarEvents" />
                <div
                    class="flex justify-end"
                >
                    <Button
                        type="button"
                        variant="subtle"
                        size="2xs"
                        class="-me-1"
                        :text="todayShortcutLabel"
                        :disabled="disabled || readOnly"
                        @click="onTodayShortcutClick"
                    />
                </div>
            </Card>
        </DateRangePickerRoot>
    </div>
</template>
