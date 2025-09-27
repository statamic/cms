<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import axios from 'axios';
import { CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody, CalendarGridHead, CalendarGridRow, CalendarHeadCell, CalendarHeader, CalendarHeading, CalendarRoot, CalendarPrev, CalendarNext } from 'reka-ui';
import { CalendarDate } from '@internationalized/date';
import CreateEntryButton from './CreateEntryButton.vue';
import { Listing, StatusIndicator } from '@/components/ui';

const props = defineProps({
    collection: { type: String, required: true },
    blueprints: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
});

const currentDate = ref(new CalendarDate(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate()));
const selectedDate = ref(null);
const entries = ref([]);
const loading = ref(false);
const viewMode = ref('month'); // 'month' or 'week'
const weekViewContainer = ref(null);


function fetchEntries() {
    loading.value = true;

    let startDate, endDate;

    if (viewMode.value === 'week') {
        // Get start of week (Sunday) and end of week (Saturday)
        const currentWeekStart = new Date(currentDate.value.year, currentDate.value.month - 1, currentDate.value.day);
        const dayOfWeek = currentWeekStart.getDay();
        const startOfWeek = new Date(currentWeekStart);
        startOfWeek.setDate(currentWeekStart.getDate() - dayOfWeek);

        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);

        startDate = startOfWeek;
        endDate = endOfWeek;
    } else {
        // Month view
        startDate = new Date(currentDate.value.year, currentDate.value.month - 1, 1);
        endDate = new Date(currentDate.value.year, currentDate.value.month, 0);
    }

    axios.get(cp_url(`collections/${props.collection}/entries`), {
        params: {
            date: `${startDate.toISOString().split('T')[0]} to ${endDate.toISOString().split('T')[0]}`,
            per_page: 1000
        }
    })
    .then(response => {
        entries.value = Object.values(response.data.data);
    })
    .catch(error => {
        console.error('Failed to fetch entries:', error);
        Statamic.$toast.error(__('Failed to load entries'));
    })
    .finally(() => {
        loading.value = false;
    });
}

function getEntriesForDate(date) {
    const dateStr = new Date(date.year, date.month - 1, date.day).toISOString().split('T')[0];
    return entries.value.filter(entry => {
        const entryDate = new Date(entry.date?.date || entry.date);
        return entryDate.toISOString().split('T')[0] === dateStr;
    });
}

function goToToday() {
    const today = new Date();
    currentDate.value = new CalendarDate(today.getFullYear(), today.getMonth() + 1, today.getDate());
}

function goToPreviousPeriod() {
    if (viewMode.value === 'week') {
        // Move back one week
        const currentDateObj = new Date(currentDate.value.year, currentDate.value.month - 1, currentDate.value.day);
        currentDateObj.setDate(currentDateObj.getDate() - 7);
        currentDate.value = new CalendarDate(currentDateObj.getFullYear(), currentDateObj.getMonth() + 1, currentDateObj.getDate());
    } else {
        // Move back one month (existing behavior)
        const currentDateObj = new Date(currentDate.value.year, currentDate.value.month - 1, currentDate.value.day);
        currentDateObj.setMonth(currentDateObj.getMonth() - 1);
        currentDate.value = new CalendarDate(currentDateObj.getFullYear(), currentDateObj.getMonth() + 1, currentDateObj.getDate());
    }
}

function goToNextPeriod() {
    if (viewMode.value === 'week') {
        // Move forward one week
        const currentDateObj = new Date(currentDate.value.year, currentDate.value.month - 1, currentDate.value.day);
        currentDateObj.setDate(currentDateObj.getDate() + 7);
        currentDate.value = new CalendarDate(currentDateObj.getFullYear(), currentDateObj.getMonth() + 1, currentDateObj.getDate());
    } else {
        // Move forward one month (existing behavior)
        const currentDateObj = new Date(currentDate.value.year, currentDate.value.month - 1, currentDate.value.day);
        currentDateObj.setMonth(currentDateObj.getMonth() + 1);
        currentDate.value = new CalendarDate(currentDateObj.getFullYear(), currentDateObj.getMonth() + 1, currentDateObj.getDate());
    }
}

function getWeekDates() {
    const currentWeekStart = new Date(currentDate.value.year, currentDate.value.month - 1, currentDate.value.day);
    const dayOfWeek = currentWeekStart.getDay();
    const startOfWeek = new Date(currentWeekStart);
    startOfWeek.setDate(currentWeekStart.getDate() - dayOfWeek);

    const weekDates = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(startOfWeek);
        date.setDate(startOfWeek.getDate() + i);
        weekDates.push(new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate()));
    }
    return weekDates;
}

function getEntriesForHour(date, hour) {
    const dateStr = new Date(date.year, date.month - 1, date.day).toISOString().split('T')[0];
    return entries.value.filter(entry => {
        const entryDate = new Date(entry.date?.date || entry.date);
        const entryDateStr = entryDate.toISOString().split('T')[0];
        if (entryDateStr !== dateStr) return false;

        const entryHour = entryDate.getHours();
        return entryHour === hour;
    });
}

function getHourLabel(hour) {
    if (hour === 0) return '12 AM';
    if (hour < 12) return `${hour} AM`;
    if (hour === 12) return '12 PM';
    return `${hour - 12} PM`;
}

function getVisibleHours() {
    // Return all 24 hours of the day
    return Array.from({length: 24}, (_, i) => i);
}

function selectDate(date) {
    selectedDate.value = date;
}

function scrollTo8AM() {
    if (weekViewContainer.value) {
        // Each hour is h-18 (72px), so 8 AM is at position 8 * 72 = 576px
        weekViewContainer.value.scrollTop = 8 * 72;
    }
}

function formatTime(dateString) {
    if (!dateString) return '';

    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
}

// Date comparison helpers
function isToday(date) {
    const today = new Date();
    const compareDate = new Date(date.year, date.month - 1, date.day);
    return compareDate.toDateString() === today.toDateString();
}

function isSelectedDate(date) {
    return selectedDate.value && selectedDate.value.toString() === date.toString();
}


const selectedDateEntries = computed(() => {
    if (!selectedDate.value) return [];
    return getEntriesForDate(selectedDate.value);
});

const columns = computed(() => [
    { label: 'Title', field: 'title', visible: true },
    { label: 'Status', field: 'status', visible: true }
]);


watch(() => [currentDate.value.year, currentDate.value.month, currentDate.value.day, viewMode.value], fetchEntries, { immediate: true });

watch(viewMode, (newMode) => {
    if (newMode === 'week') {
        // Wait for DOM to update, then scroll to 8 AM
        nextTick(() => {
            scrollTo8AM();
        });
    }
}, { immediate: true });
</script>

<template>
    <div class="@container">
        <CalendarRoot
            v-model="currentDate"
            :locale="$date.locale"
            fixed-weeks
            v-slot="{ weekDays, grid }"
            weekday-format="long"
            class="bg-gray-100 dark:bg-gray-800 rounded-2xl p-3 lg:p-6"
        >
            <CalendarHeader class="flex flex-col @3xl:flex-row items-center gap-4 @3xl:mb-4 pb-4 @3xl:pb-8">
                <CalendarHeading class="w-full @3xl:w-1/4 text-lg font-normal text-gray-800 dark:text-white text-center @3xl:text-left" />

                <div class="flex items-center justify-between w-full @3xl:flex-1 @3xl:justify-center">
                    <ui-toggle-group v-model="viewMode" class="flex">
                        <ui-toggle-item value="week" :label="__('Week')" />
                        <ui-toggle-item value="month" :label="__('Month')" />
                    </ui-toggle-group>

                    <div class="flex items-center gap-2 @3xl:hidden">
                        <ui-button icon="chevron-left" @click="goToPreviousPeriod" />
                        <ui-button @click="goToToday" :text="__('Today')" />
                        <ui-button icon="chevron-right" @click="goToNextPeriod" />
                    </div>
                </div>

                <div class="hidden @3xl:flex items-center gap-2 w-1/4 justify-end">
                    <ui-button icon="chevron-left" @click="goToPreviousPeriod" />
                    <ui-button @click="goToToday" :text="__('Today')" />
                    <ui-button icon="chevron-right" @click="goToNextPeriod" />
                </div>
            </CalendarHeader>

            <!-- Month View -->
            <CalendarGrid v-if="viewMode === 'month'" class="w-full border-collapse">
                <CalendarGridHead>
                    <CalendarGridRow class="grid grid-cols-7 gap-1 mb-2">
                        <CalendarHeadCell
                            v-for="day in weekDays"
                            :key="day"
                            class="p-2 text-center font-normal text-sm text-gray-500 dark:text-gray-400"
                        >
                            <span class="@4xl:hidden">{{ day.slice(0, 2) }}</span>
                            <span class="hidden @4xl:block">{{ day }}</span>
                        </CalendarHeadCell>
                    </CalendarGridRow>
                </CalendarGridHead>

                <CalendarGridBody class="space-y-4">
                    <template v-for="month in grid" :key="month.value.toString()">
                        <CalendarGridRow
                            v-for="(weekDates, weekIndex) in month.rows.filter(weekDates =>
                                weekDates.some(date => date.month === month.value.month)
                            )"
                            :key="`weekDate-${weekIndex}`"
                            class="grid grid-cols-7 gap-4"
                        >
                            <CalendarCell
                                v-for="weekDate in weekDates"
                                :key="weekDate.toString()"
                                :date="weekDate"
                                class="aspect-square p-2 rounded-xl shadow-ui-sm group relative"
                                :class="{
                                    'bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700': weekDate.month !== month.value.month,
                                    'bg-white dark:bg-gray-900': weekDate.month === month.value.month
                                }"
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
                                        :class="{
                                            'text-gray-400 dark:text-gray-600': outsideView,
                                            'text-gray-900 dark:text-white': !outsideView,
                                            'text-white bg-blue-600': selectedDate && selectedDate.toString() === weekDate.toString(),
                                            'text-white bg-ui-accent': today
                                        }"
                                    />

                                    <div class="@3xl:hidden w-full" v-if="getEntriesForDate(weekDate).length > 0">
                                        <div class="flex h-1 rounded-full overflow-hidden items-center justify-center">
                                            <div
                                                v-for="(entry, index) in getEntriesForDate(weekDate).slice(0, 4)"
                                                :key="entry.id"
                                                class="h-full first:rounded-s-full last:rounded-e-full"
                                                :class="{
                                                    'bg-green-500': entry.status === 'published',
                                                    'bg-gray-300': entry.status === 'draft',
                                                    'bg-purple-500': entry.status === 'scheduled'
                                                }"
                                                style="width: 25%"
                                            />
                                        </div>
                                    </div>

                                    <!-- Entries -->
                                    <div class="space-y-1.5 flex-1 overflow-scroll h-full hidden @3xl:block">
                                        <a
                                            :href="entry.edit_url"
                                            :key="entry.id"
                                            class="text-2xs @3xl:text-xs px-2 border-s-2 rounded-e-sm cursor-pointer flex flex-col"
                                            :class="{
                                                'border-green-500 hover:bg-green-50': entry.status === 'published',
                                                'border-gray-300 hover:bg-gray-50': entry.status === 'draft',
                                                'border-purple-500 hover:bg-purple-50': entry.status === 'scheduled'
                                            }"
                                            v-for="entry in getEntriesForDate(weekDate).slice(0, 3)"
                                        >
                                            <span class="line-clamp-2">
                                                {{ entry.title }}
                                            </span>
                                            <span class="hidden @4xl:block text-2xs text-gray-400 dark:text-gray-400">
                                                {{ formatTime(entry.date?.date || entry.date) }}
                                            </span>
                                        </a>
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

            <!-- Week View -->
            <div v-else class="w-full">
                <!-- Week header with days -->
                <div class="grid grid-cols-8 border border-gray-200 dark:border-gray-700 rounded-t-lg overflow-hidden">
                    <div class="p-3 text-sm bg-white font-medium text-gray-500 dark:text-gray-400"></div>
                    <div
                        v-for="date in getWeekDates()"
                        :key="date.toString()"
                        class="p-3 bg-white text-center border-l border-gray-200 dark:border-gray-700"
                        :class="{
                            'bg-blue-50 dark:bg-blue-900/20': isSelectedDate(date),
                            'bg-gray-50 dark:bg-gray-800': isToday(date)
                        }"
                    >
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ new Date(date.year, date.month - 1, date.day).toLocaleDateString($date.locale, { weekday: 'short' }) }}
                        </div>
                        <div
                            class="text-sm font-medium inline p-1"
                            :class="{
                                'text-blue-600 dark:text-blue-400': isSelectedDate(date),
                                'text-gray-900 dark:text-white': !isSelectedDate(date),
                                'rounded-full text-white bg-ui-accent': isToday(date)
                            }"
                        >
                            {{ date.day }}
                        </div>
                    </div>
                </div>

                <!-- Hourly grid -->
                <div ref="weekViewContainer" class="grid grid-cols-8 gap-0 border border-gray-200 dark:border-gray-700 rounded-b-lg overflow-auto max-h-[60vh]">
                    <!-- Hour labels column -->
                    <div class="bg-gray-50 dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
                        <div
                            v-for="hour in getVisibleHours()"
                            :key="hour"
                            class="h-18 border-b border-gray-200 dark:border-gray-700 flex items-start justify-end pr-2 pt-1"
                        >
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ getHourLabel(hour) }}
                            </span>
                        </div>
                    </div>

                    <!-- Day columns -->
                    <div
                        v-for="date in getWeekDates()"
                        :key="date.toString()"
                        class="border-l border-gray-200 dark:border-gray-700"
                    >
                        <div
                            v-for="hour in getVisibleHours()"
                            :key="hour"
                            class="h-18 border-b border-gray-200 dark:border-gray-700 relative group"
                            :class="{ 'hover:bg-gray-50 dark:hover:bg-gray-800/50': getEntriesForHour(date, hour).length === 0 }"
                            @click="selectDate(date)"
                        >
                            <!-- Entries for this hour -->
                            <div class="absolute inset-0 p-1">
                                <a
                                    v-for="entry in getEntriesForHour(date, hour)"
                                    :key="entry.id"
                                    :href="entry.edit_url"
                                    class="block text-xs p-1 rounded border-l-2 mb-1 cursor-pointer hover:shadow-sm"
                                    :class="{
                                        'border-green-500 bg-green-50 dark:bg-green-900/20': entry.status === 'published',
                                        'border-gray-300 bg-gray-50 dark:bg-gray-800': entry.status === 'draft',
                                        'border-purple-500 bg-purple-50 dark:bg-purple-900/20': entry.status === 'scheduled'
                                    }"
                                >
                                    <div class="font-medium line-clamp-2">{{ entry.title }}</div>
                                </a>
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
        </CalendarRoot>
         <!-- Mobile entries list -->
        <div class="@3xl:hidden mt-6" v-if="selectedDate">
            <ui-heading size="lg" class="flex justify-center pb-4">
                {{ new Date(selectedDate.year, selectedDate.month - 1, selectedDate.day).toLocaleDateString($date.locale, {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) }}
            </ui-heading>

            <Listing
                :items="selectedDateEntries"
                :columns="columns"
                :allow-search="false"
                :allow-customizing-columns="false"
                :show-pagination-totals="false"
                :show-pagination-page-links="false"
                :show-pagination-per-page-selector="false"
            >
                <template #cell-title="{ row: entry, isColumnVisible }">
                    <a :href="entry.edit_url" v-text="entry.title" />
                </template>
                <template #cell-status="{ row: entry }">
                    <StatusIndicator :status="entry.status" :show-dot="false" show-label />
                </template>
            </Listing>
        </div>
    </div>
</template>
