<template>
    <div class="w-full">
        <!-- Week header with days -->
        <div class="grid grid-cols-8 border border-gray-200 dark:border-gray-700 rounded-t-lg overflow-hidden">
            <div class="p-3 text-sm bg-gray-50 dark:bg-gray-900/10 font-medium text-gray-500 dark:text-gray-400"></div>
            <div
                v-for="date in weekDates"
                :key="date.toString()"
                class="p-3 bg-gray-50 dark:bg-gray-900/10 text-center border-l border-gray-200 dark:border-gray-700"
                :class="headerClasses(date)"
            >
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ new Date(date.year, date.month - 1, date.day).toLocaleDateString($date.locale, { weekday: 'short' }) }}
                </div>
                <div
                    class="text-sm font-medium inline p-1"
                    :class="dateNumberClasses(date)"
                >
                    {{ date.day }}
                </div>
            </div>
        </div>

        <!-- Hourly grid -->
        <div ref="weekViewContainer" class="grid grid-cols-8 gap-0 border border-gray-200 dark:border-gray-700 rounded-b-lg overflow-auto max-h-[60vh]">
            <!-- Hour labels column -->
            <div class="bg-gray-50 dark:bg-gray-900/10">
                <div
                    v-for="hour in visibleHours"
                    :key="hour"
                    class="h-12 border-b border-gray-200 dark:border-gray-700 flex items-start justify-end pr-2 pt-1"
                >
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ getHourLabel(hour) }}
                    </span>
                </div>
            </div>

            <!-- Day columns -->
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
                    @dragover="handleDragOver"
                    @dragenter="handleDragEnter($event, { date, hour })"
                    @dragleave="handleDragLeave"
                    @drop="handleDrop($event, date, hour)"
                >
                    <!-- Entries for this hour -->
                    <div class="absolute inset-0 p-1 overflow-scroll">
                        <CalendarWeekEntry
                            v-for="entry in getEntriesForHour(date, hour)"
                            :key="entry.id"
                            :entry="entry"
                            @dragstart="handleEntryDragStart"
                        />
                    </div>

                    <!-- Create entry button (shows on hover) -->
                    <div v-if="getEntriesForHour(date, hour).length === 0" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <CreateEntryButton
                            :url="`${createUrl}?date=${date.year}-${String(date.month).padStart(2, '0')}-${String(date.day).padStart(2, '0')}&time=${String(hour).padStart(2, '0')}:00`"
                            :blueprints="blueprints"
                            variant="subtle"
                            size="sm"
                            :custom-trigger="true"
                        >
                            <template #trigger="{ create }">
                                <ui-button icon="plus" size="sm" variant="subtle" @click="create" />
                            </template>
                        </CreateEntryButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, defineExpose } from 'vue';
import CalendarWeekEntry from './CalendarWeekEntry.vue';
import CreateEntryButton from './CreateEntryButton.vue';
import { useCalendarDates } from '@/composables/useCalendarDates';

const props = defineProps({
    weekDates: { type: Array, required: true },
    entries: { type: Array, required: true },
    pendingDateChanges: { type: Map, required: true },
    selectedDate: { type: Object, default: null },
    dragOverTarget: { type: Object, default: null },
    createUrl: { type: String, required: true },
    blueprints: { type: Array, default: () => [] },
    entriesByHour: { type: Object, required: true }
});

const emit = defineEmits(['select-date', 'entry-dragstart', 'drag-over', 'drag-enter', 'drag-leave', 'drop']);

const { formatDateString, getVisibleHours, getHourLabel, isToday } = useCalendarDates();

const visibleHours = getVisibleHours();

const getEntriesForHour = (date, hour) => {
    const key = `${date.toString()}-${hour}`;
    return props.entriesByHour[key] || [];
};

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
    'bg-blue-50 dark:bg-blue-900/20': isDragOverHour(date, hour)
});

const isSelectedDate = (date) => {
    return props.selectedDate && props.selectedDate.toString() === date.toString();
};

const isDragOverHour = (date, hour) => {
    return props.dragOverTarget &&
           props.dragOverTarget.date &&
           props.dragOverTarget.date.toString() === date.toString() &&
           props.dragOverTarget.hour === hour;
};

const selectDate = (date) => {
    emit('select-date', date);
};

const handleEntryDragStart = (event, entry) => {
    emit('entry-dragstart', event, entry);
};

const handleDragOver = (event) => {
    emit('drag-over', event);
};

const handleDragEnter = (event, target) => {
    emit('drag-enter', event, target);
};

const handleDragLeave = (event) => {
    emit('drag-leave', event);
};

const handleDrop = (event, targetDate, targetHour) => {
    emit('drop', event, targetDate, targetHour);
};

// Expose the container ref for parent component to access
const weekViewContainer = ref(null);
defineExpose({ weekViewContainer });
</script>
