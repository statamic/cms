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
                        <span class="@4xl:hidden">{{ day.slice(0, 2) }}</span>
                        <span class="hidden @4xl:block">{{ day }}</span>
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
                    class="grid grid-cols-7 gap-3"
                >
                    <CalendarCell
                        v-for="weekDate in weekDates"
                        :key="weekDate.toString()"
                        :date="weekDate"
                        class="aspect-square p-2 rounded-xl ring ring-gray-200 dark:ring-gray-700 shadow-ui-sm group relative"
                        :class="cellClasses(weekDate, month.value)"
                        @dragover="handleDragOver"
                        @dragenter="handleDragEnter($event, weekDate)"
                        @dragleave="handleDragLeave"
                        @drop="handleDrop($event, weekDate)"
                    >
                        <CalendarCellTrigger
                            :day="weekDate"
                            :month="month.value"
                            class="w-full h-full flex flex-col items-center justify-center @3xl:items-start @3xl:justify-start"
                            v-slot="{ dayValue, selected, today, outsideView }"
                            @click="selectDate(weekDate)"
                        >
                            <!-- Date number -->
                            <div
                                class="text-sm mb-1 rounded-full size-6 flex items-center justify-center"
                                v-text="dayValue"
                                :class="dateNumberClasses(weekDate, selected, today, outsideView)"
                            />

                            <!-- Mobile entry indicators -->
                            <div class="@3xl:hidden w-full" v-if="getEntriesForDate(weekDate).length > 0">
                                <div class="flex h-1 rounded-full overflow-hidden items-center justify-center">
                                    <div
                                        v-for="(entry, index) in getEntriesForDate(weekDate).slice(0, 4)"
                                        :key="entry.id"
                                        class="h-full first:rounded-s-full last:rounded-e-full"
                                        :class="entryStatusClasses(entry.status)"
                                        style="width: 25%"
                                    />
                                </div>
                            </div>

                            <!-- Desktop entries -->
                            <div class="space-y-1.5 flex-1 overflow-scroll h-full hidden @3xl:block">
                                <CalendarEntry
                                    v-for="entry in getEntriesForDate(weekDate).slice(0, 3)"
                                    :key="entry.id"
                                    :entry="entry"
                                    @dragstart="handleEntryDragStart"
                                />
                                <div v-if="getEntriesForDate(weekDate).length > 5" class="text-xs text-gray-500 dark:text-gray-400">
                                    +{{ getEntriesForDate(weekDate).length - 5 }} {{ __('more') }}
                                </div>
                            </div>
                        </CalendarCellTrigger>

                        <!-- Create entry button (shows on hover) -->
                        <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity hidden @3xl:block">
                            <CreateEntryButton
                                :url="`${createUrl}?date=${weekDate.year}-${String(weekDate.month).padStart(2, '0')}-${String(weekDate.day).padStart(2, '0')}`"
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
                    </CalendarCell>
                </CalendarGridRow>
            </template>
        </CalendarGridBody>
    </CalendarGrid>
</template>

<script setup>
import { computed } from 'vue';
import { CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody, CalendarGridHead, CalendarGridRow, CalendarHeadCell } from 'reka-ui';
import CalendarEntry from './CalendarEntry.vue';
import CreateEntryButton from './CreateEntryButton.vue';
import { useCalendarDates } from '@/composables/useCalendarDates';

const props = defineProps({
    weekDays: { type: Array, required: true },
    grid: { type: Array, required: true },
    entries: { type: Array, required: true },
    pendingDateChanges: { type: Map, required: true },
    selectedDate: { type: Object, default: null },
    dragOverTarget: { type: Object, default: null },
    createUrl: { type: String, required: true },
    blueprints: { type: Array, default: () => [] }
});

const emit = defineEmits(['select-date', 'entry-dragstart', 'drag-over', 'drag-enter', 'drag-leave', 'drop']);

const { formatDateString, isToday } = useCalendarDates();

const isCurrentDay = (dayIndex) => {
    const today = new Date();
    const currentDayName = today.toLocaleDateString('en-US', {weekday: 'long'});
    
    // Find the index of today's day name in the weekDays array
    const todayIndex = props.weekDays.findIndex(day => 
        day.toLowerCase() === currentDayName.toLowerCase()
    );
    
    return dayIndex === todayIndex;
};

const getEntriesForDate = (date) => {
    const dateStr = formatDateString(date);
    return props.entries.filter(entry => {
        // Check if this entry has a pending date change
        if (props.pendingDateChanges.has(entry.id)) {
            const newDate = props.pendingDateChanges.get(entry.id);
            return newDate.toISOString().split('T')[0] === dateStr;
        }

        const entryDate = new Date(entry.date?.date || entry.date);
        return entryDate.toISOString().split('T')[0] === dateStr;
    });
};

const cellClasses = (weekDate, monthValue) => ({
    'bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700': weekDate.month !== monthValue.month,
    'bg-white dark:bg-gray-900': weekDate.month === monthValue.month,
    'bg-ui-accent/10! border border-ui-accent!': isToday(weekDate),
    'border-2 border-blue-400 bg-blue-50 dark:bg-blue-900/20': isDragOverDate(weekDate)
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

const isDragOverDate = (date) => {
    return props.dragOverTarget && props.dragOverTarget.toString() === date.toString();
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

const handleDrop = (event, targetDate) => {
    emit('drop', event, targetDate);
};
</script>
