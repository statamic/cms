<script setup>
import { computed, ref, unref } from 'vue';
import {
    injectCalendarRootContext,
    injectRangeCalendarRootContext,
    MonthPickerCell,
    MonthPickerCellTrigger,
    MonthPickerGrid,
    MonthPickerGridBody,
    MonthPickerGridRow,
    MonthPickerHeader,
    MonthPickerHeading,
    MonthPickerNext,
    MonthPickerPrev,
    MonthPickerRoot,
    YearPickerCell,
    YearPickerCellTrigger,
    YearPickerGrid,
    YearPickerGridBody,
    YearPickerGridRow,
    YearPickerHeader,
    YearPickerHeading,
    YearPickerNext,
    YearPickerPrev,
    YearPickerRoot,
} from 'reka-ui';
import Icon from '../Icon/Icon.vue';
import Popover from '../Popover.vue';

defineOptions({ name: 'CalendarMonthYearPicker' });

const calendarRoot = injectCalendarRootContext(null) ?? injectRangeCalendarRootContext(null);

const open = ref(false);
const view = ref('month');
const placeholder = ref(null);

const disabled = computed(() => !calendarRoot || unref(calendarRoot.disabled) || unref(calendarRoot.readonly));

const pickerBindings = computed(() => ({
    minValue: unref(calendarRoot.minValue),
    maxValue: unref(calendarRoot.maxValue),
    locale: unref(calendarRoot.locale),
    preventDeselect: true,
    initialFocus: true,
}));

const navigationButtonClass =
    'inline-flex size-7.5 cursor-pointer items-center justify-center rounded-md hover:bg-gray-50 active:scale-90 disabled:pointer-events-none disabled:opacity-40 dark:hover:bg-gray-925';

const cellTriggerClass = [
    'relative flex h-9 w-full cursor-pointer items-center justify-center rounded-lg text-sm whitespace-nowrap text-gray-925 outline-hidden dark:text-white',
    'data-selected:bg-gray-800! data-selected:text-white dark:data-selected:bg-gray-200! dark:data-selected:text-gray-925',
    'hover:bg-gray-100 focus-visible:bg-gray-200 dark:hover:bg-black dark:focus-visible:bg-black',
    'data-disabled:pointer-events-none data-disabled:text-gray-400 dark:data-disabled:text-gray-600',
    'before:absolute before:top-[3px] before:hidden before:h-1 before:w-1 before:rounded-lg',
    'data-today:before:block data-today:before:bg-green-500',
];

function updateOpen(isOpen) {
    open.value = isOpen;

    if (isOpen) {
        view.value = 'month';
        placeholder.value = unref(calendarRoot.placeholder);
    }
}

function selectYear(date) {
    placeholder.value = date;
    view.value = 'month';
}

function selectMonth(date) {
    calendarRoot.onPlaceholderChange(date);
    open.value = false;
}
</script>

<template>
    <Popover :open align="start" :offset="6" class="w-72" @update:open="updateOpen">
        <template #trigger>
            <button
                type="button"
                class="-ms-2 inline-flex cursor-pointer items-center gap-1 rounded-md px-2 py-1 hover:bg-gray-50 disabled:pointer-events-none dark:hover:bg-gray-925"
                :disabled="disabled"
            >
                <slot />
                <Icon v-if="!disabled" name="chevron-down" class="size-3.5 text-gray-500 dark:text-gray-400" />
            </button>
        </template>

        <MonthPickerRoot
            v-if="view === 'month'"
            v-bind="pickerBindings"
            v-model:placeholder="placeholder"
            :model-value="unref(calendarRoot.placeholder)"
            v-slot="{ grid }"
            @update:model-value="selectMonth"
        >
            <MonthPickerHeader class="flex items-center justify-between pb-3">
                <button
                    type="button"
                    class="-ms-2 inline-flex cursor-pointer items-center gap-1 rounded-md px-2 py-1 text-sm font-medium text-gray-925 hover:bg-gray-50 dark:text-white dark:hover:bg-gray-925"
                    @click="view = 'year'"
                >
                    <MonthPickerHeading />
                    <Icon name="chevron-down" class="size-3.5 text-gray-500 dark:text-gray-400" />
                </button>
                <div class="inline-flex items-center">
                    <MonthPickerPrev :class="navigationButtonClass">
                        <Icon name="chevron-left" class="size-4" />
                    </MonthPickerPrev>
                    <MonthPickerNext :class="navigationButtonClass">
                        <Icon name="chevron-right" class="size-4" />
                    </MonthPickerNext>
                </div>
            </MonthPickerHeader>
            <MonthPickerGrid class="w-full select-none">
                <MonthPickerGridBody class="grid gap-1">
                    <MonthPickerGridRow v-for="(months, index) in grid.rows" :key="index" class="grid grid-cols-4 gap-1">
                        <MonthPickerCell v-for="month in months" :key="month.toString()" :date="month">
                            <MonthPickerCellTrigger :month :class="cellTriggerClass" />
                        </MonthPickerCell>
                    </MonthPickerGridRow>
                </MonthPickerGridBody>
            </MonthPickerGrid>
        </MonthPickerRoot>

        <YearPickerRoot
            v-else
            v-bind="pickerBindings"
            :default-placeholder="placeholder"
            :model-value="placeholder"
            v-slot="{ grid }"
            @update:model-value="selectYear"
        >
            <YearPickerHeader class="flex items-center justify-between pb-3">
                <YearPickerHeading class="text-sm font-medium text-gray-925 dark:text-white" />
                <div class="inline-flex items-center">
                    <YearPickerPrev :class="navigationButtonClass">
                        <Icon name="chevron-left" class="size-4" />
                    </YearPickerPrev>
                    <YearPickerNext :class="navigationButtonClass">
                        <Icon name="chevron-right" class="size-4" />
                    </YearPickerNext>
                </div>
            </YearPickerHeader>
            <YearPickerGrid class="w-full select-none">
                <YearPickerGridBody class="grid gap-1">
                    <YearPickerGridRow v-for="(years, index) in grid.rows" :key="index" class="grid grid-cols-4 gap-1">
                        <YearPickerCell v-for="year in years" :key="year.toString()" :date="year">
                            <YearPickerCellTrigger :year :class="cellTriggerClass" />
                        </YearPickerCell>
                    </YearPickerGridRow>
                </YearPickerGridBody>
            </YearPickerGrid>
        </YearPickerRoot>
    </Popover>
</template>
