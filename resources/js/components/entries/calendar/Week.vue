<script setup>
import { ref } from 'vue';
import CalendarEntry from './WeekEntry.vue';
import CreateEntryButton from '../CreateEntryButton.vue';
import { Button } from '@ui';
import {
    isToday,
    getCreateUrlDateParam,
    formatDateString,
} from './calendar.js';
import DateFormatter from '@/components/DateFormatter.js';

const props = defineProps({
    weekDates: { type: Array, required: true },
    entries: { type: Array, required: true },
    selectedDate: { type: Object, default: null },
    createUrl: { type: String, required: true },
    blueprints: { type: Array, default: () => [] },
});

const emit = defineEmits(['select-date']);

const $date = new DateFormatter;

const visibleHours = Array.from({ length: 24 }, (_, i) => i);

function getEntriesForHour(date, hour) {
    const dateStr = formatDateString(date);
    return props.entries.filter(entry => {
        const entryDate = new Date(entry.date?.date || entry.date);
        const entryDateStr = entryDate.toISOString().split('T')[0];
        if (entryDateStr !== dateStr) return false;

        const entryHour = entryDate.getHours();
        return entryHour === hour;
    });
}

const headerClasses = (date) => ({
    'bg-blue-50 dark:bg-blue-900/20': isSelectedDate(date),
    'bg-gray-50 dark:bg-gray-800': isToday(date)
});

const dateNumberClasses = (date) => ({
    'text-blue-600 dark:text-blue-400': isSelectedDate(date),
    'text-gray-900 dark:text-white': !isSelectedDate(date),
    'rounded-full text-white bg-ui-accent': isToday(date)
});

const hourCellClasses = (date, hour) => ({
    'hover:bg-gray-50 dark:hover:bg-gray-800/50': getEntriesForHour(date, hour).length === 0,
});

const isSelectedDate = (date) => {
    return props.selectedDate && props.selectedDate.toString() === date.toString();
};

const selectDate = (date) => {
    emit('select-date', date);
};

function getHourLabel(hour) {
    const date = new Date
    date.setHours(hour, 0, 0, 0);
    return DateFormatter.format(date, { hour: 'numeric', hour12: true });
}
</script>

<template>
    <div class="w-full">
        <div class="grid grid-cols-8 border border-gray-200 dark:border-gray-700 rounded-t-lg overflow-hidden">
            <div class="p-3 text-sm bg-gray-50 dark:bg-gray-900/10 font-medium text-gray-500 dark:text-gray-400"></div>
            <div
                v-for="date in weekDates"
                :key="date.toString()"
                class="p-3 bg-gray-50 dark:bg-gray-900/10 text-center border-l border-gray-200 dark:border-gray-700"
                :class="headerClasses(date)"
            >
                <div class="text-xs text-gray-500 dark:text-gray-400" v-text="$date.format(date, { weekday: 'short'})" />
                <div
                    class="text-sm font-medium inline p-1"
                    :class="dateNumberClasses(date)"
                    v-text="date.day"
                />
            </div>
        </div>

        <div ref="weekViewContainer" class="grid grid-cols-8 gap-0 border border-gray-200 dark:border-gray-700 rounded-b-lg overflow-auto max-h-[60vh]">
            <div class="bg-gray-50 dark:bg-gray-900/10">
                <div
                    v-for="hour in visibleHours"
                    :key="hour"
                    class="h-12 border-b border-gray-200 dark:border-gray-700 flex items-start justify-end pr-2 pt-1"
                >
                    <span class="text-xs text-gray-500 dark:text-gray-400" v-text="getHourLabel(hour)" />
                </div>
            </div>

            <div
                v-for="date in weekDates"
                :key="date.toString()"
                class="bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-700"
            >
                <div
                    v-for="hour in visibleHours"
                    :key="hour"
                    class="h-12 border-b border-gray-200 dark:border-gray-700 relative group"
                    :class="hourCellClasses(date, hour)"
                    @click="selectDate(date)"
                >
                    <div class="absolute inset-0 p-1 overflow-scroll">
                        <CalendarEntry
                            v-for="entry in getEntriesForHour(date, hour)"
                            :key="entry.id"
                            :entry="entry"
                        />
                    </div>

                    <div v-if="getEntriesForHour(date, hour).length === 0" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <CreateEntryButton
                            :params="{ values: { date: getCreateUrlDateParam(date, hour) } }"
                            :blueprints="blueprints"
                            variant="subtle"
                            size="sm"
                        >
                            <template #trigger="{ create }">
                                <Button icon="plus" size="sm" variant="subtle" @click="create" />
                            </template>
                        </CreateEntryButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
