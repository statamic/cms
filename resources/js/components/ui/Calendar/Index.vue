<script setup>
import { ref, computed } from 'vue';
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarHeader,
    CalendarHeading,
    CalendarRoot,
    CalendarPrev,
    CalendarNext,
} from 'reka-ui';
import { parseDate } from '@internationalized/date';

defineOptions({ name: 'Calendar' });

const props = defineProps({
    modelValue: { type: [String, Object], default: null },
    min: { type: [String, Object], default: null },
    max: { type: [String, Object], default: null },
});

const emit = defineEmits(['update:modelValue']);

const minValue = computed(() =>
    props.min ? (typeof props.min === 'string' ? parseDate(props.min) : props.min) : null,
);
const maxValue = computed(() =>
    props.max ? (typeof props.max === 'string' ? parseDate(props.max) : props.max) : null,
);
</script>

<template>
    <CalendarRoot
        :model-value="modelValue"
        v-slot="{ weekDays, grid }"
        :minValue="minValue"
        :maxValue="maxValue"
        :locale="$date.locale"
        fixed-weeks
        @update:model-value="emit('update:modelValue', $event)"
    >
        <CalendarHeader class="flex items-center justify-between">
            <CalendarHeading class="text-sm font-medium text-black dark:text-white" />
            <div>
                <CalendarPrev
                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-md hover:bg-gray-50 active:scale-90 dark:hover:bg-gray-950"
                >
                    <ui-icon name="chevron-left" class="size-4" />
                </CalendarPrev>
                <CalendarNext
                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-md hover:bg-gray-50 active:scale-90 dark:hover:bg-gray-950"
                >
                    <ui-icon name="chevron-right" class="size-4" />
                </CalendarNext>
            </div>
        </CalendarHeader>

        <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:space-y-0 sm:space-x-4">
            <CalendarGrid
                v-for="month in grid"
                :key="month.value.toString()"
                class="w-full border-collapse space-y-1 select-none"
            >
                <CalendarGridHead>
                    <CalendarGridRow class="mb-1 grid w-full grid-cols-7">
                        <CalendarHeadCell
                            v-for="day in weekDays"
                            :key="day"
                            class="rounded-md text-xs text-black dark:text-white"
                        >
                            {{ day }}
                        </CalendarHeadCell>
                    </CalendarGridRow>
                </CalendarGridHead>

                <CalendarGridBody class="grid space-y-1">
                    <CalendarGridRow
                        v-for="(weekDates, index) in month.rows"
                        :key="`weekDate-${index}`"
                        class="grid grid-cols-7"
                    >
                        <CalendarCell
                            v-for="weekDate in weekDates"
                            :key="weekDate.toString()"
                            :date="weekDate"
                            class="relative text-center text-sm"
                        >
                            <CalendarCellTrigger
                                :day="weekDate"
                                :month="month.value"
                                :class="[
                                    'relative flex size-8 items-center justify-center rounded-lg text-sm font-normal whitespace-nowrap text-black outline-hidden dark:text-white',
                                    'data-outside-view:text-gray-400 dark:data-outside-view:text-gray-600',
                                    'data-selected:bg-gray-800! data-selected:text-white dark:data-selected:bg-gray-200! dark:data-selected:text-black',
                                    'hover:bg-gray-100 data-highlighted:bg-gray-200 dark:hover:bg-black dark:data-highlighted:bg-black',
                                    'data-disabled:pointer-events-none data-disabled:hover:bg-transparent',
                                    'data-disabled:text-gray-400 dark:data-disabled:text-gray-600',
                                    'data-unavailable:pointer-events-none data-unavailable:text-black/30 data-unavailable:line-through',
                                    'before:absolute before:top-[3px] before:hidden before:h-1 before:w-1 before:rounded-lg before:bg-white',
                                    'data-today:before:block data-today:before:bg-green-600',
                                ]"
                            />
                        </CalendarCell>
                    </CalendarGridRow>
                </CalendarGridBody>
            </CalendarGrid>
        </div>
    </CalendarRoot>
</template>
