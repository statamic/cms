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
import { parseAbsolute } from '@internationalized/date';
import { Icon } from '@/components/ui';

defineOptions({ name: 'Calendar' });

const props = defineProps({
    modelValue: { type: [String, Object], default: null },
    min: { type: [String, Object], default: null },
    max: { type: [String, Object], default: null },
    components: { type: Object, default: () => ({}) },
    numberOfMonths: { type: Number, default: 1 },
    inline: { type: Boolean, default: false },
});

const components = computed(() => ({
    CalendarRoot: props.components.Root || CalendarRoot,
    CalendarHeader: props.components.Header || CalendarHeader,
    CalendarHeading: props.components.Heading || CalendarHeading,
    CalendarPrev: props.components.Prev || CalendarPrev,
    CalendarNext: props.components.Next || CalendarNext,
    CalendarGrid: props.components.Grid || CalendarGrid,
    CalendarGridHead: props.components.GridHead || CalendarGridHead,
    CalendarGridBody: props.components.GridBody || CalendarGridBody,
    CalendarGridRow: props.components.GridRow || CalendarGridRow,
    CalendarHeadCell: props.components.HeadCell || CalendarHeadCell,
    CalendarCell: props.components.Cell || CalendarCell,
    CalendarCellTrigger: props.components.CellTrigger || CalendarCellTrigger,
}));

const emit = defineEmits(['update:modelValue']);

const minValue = computed(() =>
    props.min ? (typeof props.min === 'string' ? parseAbsolute(props.min) : props.min) : null,
);
const maxValue = computed(() =>
    props.max ? (typeof props.max === 'string' ? parseAbsolute(props.max) : props.max) : null,
);

const gridStyle = computed(() => {
    const months = props.numberOfMonths;

    // For 1-2 months: single row with fixed columns
    if (months <= 2) {
        return {
            'grid-template-columns': `repeat(${months}, minmax(250px, 1fr))`,
            'grid-template-rows': 'auto'
        };
    }

    // For 3+ months: responsive grid with auto-fit
    return {
        'grid-template-columns': 'repeat(auto-fit, minmax(250px, 1fr))',
        'grid-template-rows': 'auto'
    };
});
</script>

<template>
    <Component
        :is="components.CalendarRoot"
        :model-value="modelValue"
        v-slot="{ weekDays, grid }"
        :minValue="minValue"
        :maxValue="maxValue"
        :locale="$date.locale"
        fixed-weeks
        :number-of-months="inline ? numberOfMonths : 1"
        @update:model-value="emit('update:modelValue', $event)"
    >
        <Component :is="components.CalendarHeader" class="flex items-center justify-between">
            <Component :is="components.CalendarHeading" class="text-sm font-medium text-black dark:text-white" />
            <div>
                <Component
                    :is="components.CalendarPrev"
                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-md hover:bg-gray-50 active:scale-90 dark:hover:bg-gray-950"
                >
                    <Icon name="ui/chevron-left" class="size-4" />
                </Component>
                <Component
                    :is="components.CalendarNext"
                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-md hover:bg-gray-50 active:scale-90 dark:hover:bg-gray-950"
                >
                    <Icon name="ui/chevron-right" class="size-4" />
                </Component>
            </div>
        </Component>

        <div class="grid gap-8" :style="gridStyle">
            <Component
                :is="components.CalendarGrid"
                v-for="month in grid"
                :key="month.value.toString()"
                class="w-full border-collapse space-y-1 select-none"
            >
                <Component :is="components.CalendarGridHead">
                    <ui-badge variant="flat" class="mb-2" v-if="inline && numberOfMonths > 1">
                        {{ new Date(month.value.toString()).toLocaleString($date.locale, { month: 'long' }) }}
                    </ui-badge>
                    <Component :is="components.CalendarGridRow" class="mb-1 grid w-full grid-cols-7">
                        <Component
                            :is="components.CalendarHeadCell"
                            v-for="day in weekDays"
                            :key="day"
                            class="rounded-md text-xs text-black dark:text-white"
                        >
                            {{ day }}
                        </Component>
                    </Component>
                </Component>

                <Component :is="components.CalendarGridBody" class="grid space-y-1">
                    <Component
                        :is="components.CalendarGridRow"
                        v-for="(weekDates, index) in month.rows"
                        :key="`weekDate-${index}`"
                        class="grid grid-cols-7"
                    >
                        <Component
                            :is="components.CalendarCell"
                            v-for="weekDate in weekDates"
                            :key="weekDate.toString()"
                            :date="weekDate"
                            class="relative flex justify-center text-sm"
                        >
                            <Component
                                :is="components.CalendarCellTrigger"
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
                        </Component>
                    </Component>
                </Component>
            </Component>
        </div>
    </Component>
</template>
