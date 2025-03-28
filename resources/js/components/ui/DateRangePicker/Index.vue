<script setup>
import { computed } from 'vue';
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
import { parseDate } from '@internationalized/date';
import { WithField, Card, Button } from '@statamic/ui';

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
    date: { type: String, default: null },
    badge: { type: String, default: null },
    description: { type: String, default: null },
    label: { type: String, default: null },
    required: { type: Boolean, default: false },
    modelValue: { type: [Object, String], default: null },
    min: { type: [String, Object], default: null },
    max: { type: [String, Object], default: null },
    granularity: { type: String, default: null },
});

const minValue = computed(() =>
    props.min ? (typeof props.min === 'string' ? parseDate(props.min) : props.min) : null,
);
const maxValue = computed(() =>
    props.max ? (typeof props.max === 'string' ? parseDate(props.max) : props.max) : null,
);
</script>

<template>
    <WithField :label :description :required :badge>
        <div class="group/input relative block w-full" data-ui-input>
            <DateRangePickerRoot
                :modelValue="modelValue"
                :granularity="granularity"
                :locale="$date.locale"
                @update:model-value="emit('update:modelValue', $event)"
                v-bind="$attrs"
            >
                <DateRangePickerField v-slot="{ segments }" class="w-full">
                    <div
                        :class="[
                            'flex w-full bg-white dark:bg-gray-900',
                            'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black',
                            'leading-[1.375rem] text-gray-600 dark:text-gray-300',
                            'shadow-ui-sm not-prose h-10 rounded-lg py-2 ps-3 pe-10 disabled:shadow-none',
                            'data-invalid:border-red-500',
                        ]"
                    >
                        <template v-for="item in segments.start" :key="item.part">
                            <DateRangePickerInput v-if="item.part === 'literal'" :part="item.part" type="start">
                                {{ item.value }}
                            </DateRangePickerInput>
                            <DateRangePickerInput
                                v-else
                                :part="item.part"
                                class="rounded-sm px-0.25 py-0.5 focus:bg-gray-50 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-gray-800 dark:data-placeholder:text-gray-400"
                                :class="{
                                    'px-0.5!': item.part === 'month' || item.part === 'year' || item.part === 'day',
                                }"
                                type="start"
                            >
                                {{ item.value }}
                            </DateRangePickerInput>
                        </template>
                        <span class="mx-2"> - </span>
                        <template v-for="item in segments.end" :key="item.part">
                            <DateRangePickerInput v-if="item.part === 'literal'" :part="item.part" type="end">
                                {{ item.value }}
                            </DateRangePickerInput>
                            <DateRangePickerInput
                                v-else
                                :part="item.part"
                                class="rounded-sm px-0.25 py-0.5 focus:bg-gray-50 focus:outline-hidden data-placeholder:text-gray-600 dark:focus:bg-gray-800 dark:data-placeholder:text-gray-400"
                                :class="{
                                    'px-0.5!': item.part === 'month' || item.part === 'year' || item.part === 'day',
                                }"
                                type="end"
                            >
                                {{ item.value }}
                            </DateRangePickerInput>
                        </template>
                    </div>
                    <DateRangePickerTrigger
                        class="absolute end-1 top-1 bottom-1 flex items-center justify-center rounded-lg px-2 text-gray-400 outline-hidden hover:bg-gray-50 focus:bg-gray-50 dark:hover:bg-gray-900 dark:focus:bg-gray-900"
                    >
                        <ui-icon name="calendar" class="h-4 w-4" />
                    </DateRangePickerTrigger>
                </DateRangePickerField>

                <DateRangePickerContent
                    :side-offset="4"
                    class="data-[state=open]:data-[side=top]:animate-slideDownAndFade data-[state=open]:data-[side=right]:animate-slideLeftAndFade data-[state=open]:data-[side=bottom]:animate-slideUpAndFade data-[state=open]:data-[side=left]:animate-slideRightAndFade will-change-[transform,opacity]"
                >
                    <Card>
                        <DateRangePickerCalendar
                            :model-value="modelValue"
                            v-slot="{ weekDays, grid }"
                            :minValue="minValue"
                            :maxValue="maxValue"
                            :locale="$date.locale"
                            fixed-weeks
                            @update:model-value="emit('update:modelValue', $event)"
                        >
                            <DateRangePickerHeader class="flex items-center justify-between">
                                <DateRangePickerHeading class="text-sm font-medium text-black dark:text-white" />
                                <div>
                                    <DateRangePickerPrev
                                        class="inline-flex size-8 cursor-pointer items-center justify-center rounded-md hover:bg-gray-50 active:scale-90 dark:hover:bg-gray-950"
                                    >
                                        <ui-icon name="chevron-left" class="size-4" />
                                    </DateRangePickerPrev>
                                    <DateRangePickerNext
                                        class="inline-flex size-8 cursor-pointer items-center justify-center rounded-md hover:bg-gray-50 active:scale-90 dark:hover:bg-gray-950"
                                    >
                                        <ui-icon name="chevron-right" class="size-4" />
                                    </DateRangePickerNext>
                                </div>
                            </DateRangePickerHeader>

                            <div class="flex flex-col space-y-4 pt-4 sm:flex-row sm:space-y-0 sm:space-x-4">
                                <DateRangePickerGrid
                                    v-for="month in grid"
                                    :key="month.value.toString()"
                                    class="w-full border-collapse space-y-1 select-none"
                                >
                                    <DateRangePickerGridHead>
                                        <DateRangePickerGridRow class="mb-1 grid w-full grid-cols-7">
                                            <DateRangePickerHeadCell
                                                v-for="day in weekDays"
                                                :key="day"
                                                class="rounded-md text-xs text-black dark:text-white"
                                            >
                                                {{ day }}
                                            </DateRangePickerHeadCell>
                                        </DateRangePickerGridRow>
                                    </DateRangePickerGridHead>

                                    <DateRangePickerGridBody class="grid space-y-1">
                                        <DateRangePickerGridRow
                                            v-for="(weekDates, index) in month.rows"
                                            :key="`weekDate-${index}`"
                                            class="grid grid-cols-7"
                                        >
                                            <DateRangePickerCell
                                                v-for="weekDate in weekDates"
                                                :key="weekDate.toString()"
                                                :date="weekDate"
                                                class="relative text-center text-sm"
                                            >
                                                <DateRangePickerCellTrigger
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
                                            </DateRangePickerCell>
                                        </DateRangePickerGridRow>
                                    </DateRangePickerGridBody>
                                </DateRangePickerGrid>
                            </div>
                        </DateRangePickerCalendar>
                    </Card>
                </DateRangePickerContent>
            </DateRangePickerRoot>
        </div>
        <Button @click="emit('update:modelValue', null)" type="button" class="" text="Clear" size="xs" />
    </WithField>
</template>
