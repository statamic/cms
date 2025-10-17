<script setup>
import { CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody, CalendarGridHead, CalendarGridRow, CalendarHeadCell } from 'reka-ui';
import CalendarEntry from './MonthEntry.vue';
import CreateEntryButton from '../CreateEntryButton.vue';
import { Button } from '@ui';
import { formatDateString, isToday, getCreateUrlDateParam } from './calendar.js';
import DateFormatter from '@/components/DateFormatter.js';

const props = defineProps({
    weekDays: { type: Array, required: true },
    grid: { type: Array, required: true },
    entries: { type: Array, required: true },
    selectedDate: { type: Object, default: null },
    createUrl: { type: String, required: true },
    blueprints: { type: Array, default: () => [] }
});

const emit = defineEmits(['select-date']);

const isCurrentDay = (dayIndex) => {
    const currentDayName = DateFormatter.format('now', { weekday: 'long' });

    // Find the index of today's day name in the weekDays array
    const todayIndex = props.weekDays.findIndex(day =>
        day.toLowerCase() === currentDayName.toLowerCase()
    );

    return dayIndex === todayIndex;
};

const getEntriesForDate = (date) => {
    const dateStr = formatDateString(date);
    return props.entries.filter(entry => {
        const entryDate = new Date(entry.date?.date || entry.date);
        return entryDate.toISOString().split('T')[0] === dateStr;
    });
};

const weekHasEntries = (weekDates) => {
    return weekDates.some(date => getEntriesForDate(date).length > 0);
};

const cellClasses = (weekDate, monthValue) => ({
    'bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700': weekDate.month !== monthValue.month,
    'bg-white dark:bg-gray-900': weekDate.month === monthValue.month,
    'bg-ui-accent/10! border border-ui-accent!': isToday(weekDate),
});

const dateNumberClasses = (weekDate, selected, today, outsideView) => ({
    'text-gray-400 dark:text-gray-600': outsideView,
    'text-gray-900 dark:text-white': !outsideView,
    'text-white bg-blue-600': props.selectedDate && props.selectedDate.toString() === weekDate.toString(),
    'text-ui-accent': today
});

const entryStatusClasses = (status) => ({
    'bg-green-500': status === 'published',
    'bg-gray-300': status === 'draft',
    'bg-purple-500': status === 'scheduled'
});


const selectDate = (date) => {
    emit('select-date', date);
};
</script>

<template>
    <CalendarGrid class="w-full border-collapse">
        <CalendarGridHead>
            <CalendarGridRow class="grid grid-cols-7 gap-3 mb-2">
                <CalendarHeadCell
                    v-for="(day, index) in weekDays"
                    :key="day"
                    class="p-2 text-center font-medium text-sm text-gray-700 dark:text-gray-400 bg-gray-200/75 dark:bg-gray-900/75 rounded-lg"
                >
                    <div class="flex items-center justify-center gap-1">
                        <div
                            v-if="isCurrentDay(index)"
                            class="w-1.5 h-1.5 mr-1 bg-ui-accent rounded-full"
                        ></div>
                        <span class="@4xl:hidden" v-text="day.slice(0, 2)" />
                        <span class="hidden @4xl:block" v-text="day" />
                    </div>
                </CalendarHeadCell>
            </CalendarGridRow>
        </CalendarGridHead>

        <CalendarGridBody class="space-y-3 calendar-grid">
            <template v-for="month in grid" :key="month.value.toString()">
                <CalendarGridRow
                    v-for="(weekDates, weekIndex) in month.rows.filter(weekDates =>
                        weekDates.some(date => date.month === month.value.month)
                    )"
                    :key="`weekDate-${weekIndex}`"
                    :data-week-has-entries="weekHasEntries(weekDates)"
                    class="grid grid-cols-7 gap-3"
                >
                    <CalendarCell
                        v-for="weekDate in weekDates"
                        :key="weekDate.toString()"
                        :date="weekDate"
                        class="aspect-square p-2 rounded-xl ring ring-gray-200 dark:ring-gray-700 shadow-ui-sm group relative"
                        :class="cellClasses(weekDate, month.value)"
                    >
                        <CalendarCellTrigger
                            :day="weekDate"
                            :month="month.value"
                            class="w-full h-full flex flex-col items-center justify-center @3xl:items-start @3xl:justify-start"
                            v-slot="{ dayValue, selected, today, outsideView }"
                            @click="selectDate(weekDate)"
                        >
                            <div
                                class="text-sm mb-1 rounded-full size-6 flex items-center justify-center"
                                v-text="dayValue"
                                :class="dateNumberClasses(weekDate, selected, today, outsideView)"
                            />

                            <div class="@3xl:hidden w-full" v-if="getEntriesForDate(weekDate).length > 0">
                                <div class="flex h-1 rounded-full overflow-hidden items-center justify-center">
                                    <div
                                        v-for="(entry, index) in getEntriesForDate(weekDate).slice(0, 4)"
                                        :key="entry.id"
                                        class="w-1/4 h-full first:rounded-s-full last:rounded-e-full"
                                        :class="entryStatusClasses(entry.status)"
                                    />
                                </div>
                            </div>

                            <div class="space-y-1.5 flex-1 overflow-scroll h-full w-full hidden @3xl:block">
                                <CalendarEntry
                                    v-for="entry in getEntriesForDate(weekDate)"
                                    :key="entry.id"
                                    :entry="entry"
                                />
                            </div>
                        </CalendarCellTrigger>

                        <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity hidden @3xl:block">
                            <CreateEntryButton
                                :params="{ values: { date: getCreateUrlDateParam(weekDate) } }"
                                :blueprints="blueprints"
                                variant="subtle"
                                size="sm"
                            >
                                <template #trigger="{ create }">
                                    <Button icon="plus" size="sm" variant="subtle" @click="create" />
                                </template>
                            </CreateEntryButton>
                        </div>
                    </CalendarCell>
                </CalendarGridRow>
            </template>
        </CalendarGridBody>
    </CalendarGrid>
</template>

<style scoped>
@media (height < 1000px) and (width >= 1400px) {
    .calendar-grid {
        tr:not([data-week-has-entries="true"]) td {
            aspect-ratio: 2 / 1;
        }
    }
}
</style>
