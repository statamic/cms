<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import axios from 'axios';
import { CalendarHeader, CalendarHeading, CalendarRoot } from 'reka-ui';
import { CalendarDate } from '@internationalized/date';
import CalendarMonthView from './CalendarMonthView.vue';
import CalendarWeekView from './CalendarWeekView.vue';
import { Listing, StatusIndicator } from '@/components/ui';
import { useCalendarDates } from '@/composables/useCalendarDates';

const props = defineProps({
    collection: { type: String, required: true },
    blueprints: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
});

const emit = defineEmits(['changed', 'saved', 'canceled']);

const currentDate = ref(new CalendarDate(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate()));
const selectedDate = ref(null);
const entries = ref([]);
const loading = ref(false);
const error = ref(null);
const saving = ref(false);
const viewMode = ref('month'); // 'month' or 'week'
const weekViewRef = ref(null);
const pendingDateChanges = ref(new Map()); // Track entry ID -> new date
const isDirty = ref(false);
// Reactive drag state for Vue class bindings
const dragOverTarget = ref(null);

// Use composables
const {
    formatDateString,
    createDateFromCalendarDate,
    getWeekDates,
    getVisibleHours,
    getHourLabel,
    formatTime,
    isToday,
    getCurrentDateRange
} = useCalendarDates();

// ============================================================================
// API & Data Management
// ============================================================================

async function fetchEntries() {
    loading.value = true;
    error.value = null;

    try {
        // Guard against undefined values
        if (!currentDate.value || !viewMode.value) {
            console.warn('fetchEntries called with undefined values:', { currentDate: currentDate.value, viewMode: viewMode.value });
            return;
        }

        const { startDate, endDate } = getCurrentDateRange(currentDate.value, viewMode.value);

        if (!startDate || !endDate) {
            console.error('getCurrentDateRange returned undefined values:', { startDate, endDate });
            return;
        }

        const response = await axios.get(cp_url(`collections/${props.collection}/entries`), {
            params: {
                date: `${startDate.toISOString().split('T')[0]} to ${endDate.toISOString().split('T')[0]}`,
                per_page: 1000
            }
        });

        entries.value = Object.values(response.data.data);
    } catch (err) {
        error.value = err;
        console.error('Failed to fetch entries:', err);
        Statamic.$toast.error(__('Failed to load entries'));
    } finally {
        loading.value = false;
    }
}

function getEntriesForDate(date) {
    const dateStr = formatDateString(date);
    return entries.value.filter(entry => {
        // Check if this entry has a pending date change
        if (pendingDateChanges.value.has(entry.id)) {
            const newDate = pendingDateChanges.value.get(entry.id);
            return newDate.toISOString().split('T')[0] === dateStr;
        }

        const entryDate = new Date(entry.date?.date || entry.date);
        return entryDate.toISOString().split('T')[0] === dateStr;
    });
}

function getEntriesForHour(date, hour) {
    const dateStr = formatDateString(date);
    return entries.value.filter(entry => {
        // Check if this entry has a pending date change
        if (pendingDateChanges.value.has(entry.id)) {
            const newDate = pendingDateChanges.value.get(entry.id);
            const newDateStr = newDate.toISOString().split('T')[0];
            if (newDateStr !== dateStr) return false;

            const newHour = newDate.getHours();
            return newHour === hour;
        }

        const entryDate = new Date(entry.date?.date || entry.date);
        const entryDateStr = entryDate.toISOString().split('T')[0];
        if (entryDateStr !== dateStr) return false;

        const entryHour = entryDate.getHours();
        return entryHour === hour;
    });
}

// ============================================================================
// Date Navigation
// ============================================================================

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


function selectDate(date) {
    selectedDate.value = date;
}

function scrollTo8AM() {
    if (weekViewRef.value?.weekViewContainer) {
        // Each hour is h-18 (72px), so 8 AM is at position 8 * 72 = 576px
        weekViewRef.value.weekViewContainer.scrollTop = 8 * 72;
    }
}

// ============================================================================
// Date Comparison Helpers
// ============================================================================
function isSelectedDate(date) {
    return selectedDate.value && selectedDate.value.toString() === date.toString();
}

function isDragOverDate(date) {
    return dragOverTarget.value && dragOverTarget.value.toString() === date.toString();
}

function isDragOverHour(date, hour) {
    return dragOverTarget.value &&
           dragOverTarget.value.date &&
           dragOverTarget.value.date.toString() === date.toString() &&
           dragOverTarget.value.hour === hour;
}


// ============================================================================
// Drag and Drop Functions
// ============================================================================
function handleEntryDragStart(event, entry) {
    event.dataTransfer.setData('text/plain', JSON.stringify({
        entryId: entry.id,
        entryTitle: entry.title
    }));
    event.dataTransfer.effectAllowed = 'move';
}

function handleDateDrop(event, targetDate) {
    event.preventDefault();

    try {
        const data = JSON.parse(event.dataTransfer.getData('text/plain'));
        const entryId = data.entryId;

        // Find the entry
        const entry = entries.value.find(e => e.id === entryId);
        if (!entry) return;

        // Create new date with the same time but new date
        const originalDate = new Date(entry.date?.date || entry.date);
        const newDate = new Date(targetDate.year, targetDate.month - 1, targetDate.day,
                                originalDate.getHours(), originalDate.getMinutes(), originalDate.getSeconds());

        // Store the pending change
        pendingDateChanges.value.set(entryId, newDate);
        isDirty.value = true;

        // Emit change event to parent
        emit('changed');

    } catch (error) {
        console.error('Failed to handle drop:', error);
    }
}

function handleHourDrop(event, targetDate, targetHour) {
    event.preventDefault();

    try {
        const data = JSON.parse(event.dataTransfer.getData('text/plain'));
        const entryId = data.entryId;

        // Find the entry
        const entry = entries.value.find(e => e.id === entryId);
        if (!entry) return;

        // Create new date with the new date and hour
        const originalDate = new Date(entry.date?.date || entry.date);
        const newDate = new Date(targetDate.year, targetDate.month - 1, targetDate.day,
                                targetHour, originalDate.getMinutes(), originalDate.getSeconds());

        // Store the pending change
        pendingDateChanges.value.set(entryId, newDate);
        isDirty.value = true;

        // Emit change event to parent
        emit('changed');

    } catch (error) {
        console.error('Failed to handle drop:', error);
    }
}

function handleDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
}

function handleDragEnter(event, target) {
    event.preventDefault();
    dragOverTarget.value = target;
}

function handleDragLeave(event) {
    // Only clear if we're actually leaving the drop zone
    if (!event.relatedTarget || !event.currentTarget.contains(event.relatedTarget)) {
        dragOverTarget.value = null;
    }
}

function handleDrop(event, targetDate, targetHour = null) {
    event.preventDefault();
    dragOverTarget.value = null;

    if (targetHour !== null) {
        handleHourDrop(event, targetDate, targetHour);
    } else {
        handleDateDrop(event, targetDate);
    }
}

// ============================================================================
// Save and Cancel Functions
// ============================================================================
async function saveChanges() {
    if (pendingDateChanges.value.size === 0) return Promise.resolve();

    saving.value = true;
    error.value = null;

    try {
        // Update each entry individually
        const promises = Array.from(pendingDateChanges.value.entries()).map(([entryId, newDate]) => {
            // Find the entry to get its current title and slug
            const entry = entries.value.find(e => e.id === entryId);

            return axios.patch(cp_url(`collections/${props.collection}/entries/${entryId}`), {
                date: newDate.toISOString(),
                title: entry.title,
                slug: entry.slug
            });
        });

        const response = await Promise.all(promises);

        // Update local entries with the new dates
        pendingDateChanges.value.forEach((newDate, entryId) => {
            const entry = entries.value.find(e => e.id === entryId);
            if (entry) {
                entry.date = newDate.toISOString();
            }
        });

        // Clear pending changes
        pendingDateChanges.value.clear();
        isDirty.value = false;

        // Emit saved event
        emit('saved');

        Statamic.$toast.success(__('Saved'));
        return response;
    } catch (err) {
        error.value = err;
        console.error('Failed to save changes:', err);
        Statamic.$toast.error(__('Failed to save changes'));
        throw err;
    } finally {
        saving.value = false;
    }
}

function cancelChanges() {
    pendingDateChanges.value.clear();
    isDirty.value = false;
    emit('canceled');
}

// ============================================================================
// Component API
// ============================================================================

// Expose methods to parent
defineExpose({
    saveChanges,
    cancelChanges,
    isDirty: () => isDirty.value
});

// ============================================================================
// Computed Properties
// ============================================================================
const selectedDateEntries = computed(() => {
    if (!selectedDate.value) return [];
    return getEntriesForDate(selectedDate.value);
});

const columns = computed(() => [
    { label: 'Title', field: 'title', visible: true },
    { label: 'Status', field: 'status', visible: true }
]);

// Memoize entries for each hour to prevent re-computation during drag
const entriesByHour = computed(() => {
    const result = {};
    getWeekDates(currentDate.value).forEach(date => {
        getVisibleHours().forEach(hour => {
            const key = `${date.toString()}-${hour}`;
            result[key] = getEntriesForHour(date, hour);
        });
    });
    return result;
});

// Group entries by date for efficient lookup
const entriesByDate = computed(() => {
    const grouped = {};
    entries.value.forEach(entry => {
        const dateStr = formatDateString(entry.date);
        if (!grouped[dateStr]) grouped[dateStr] = [];
        grouped[dateStr].push(entry);
    });
    return grouped;
});

// Current view date range
const currentDateRange = computed(() => {
    return getCurrentDateRange(currentDate.value, viewMode.value);
});

// ============================================================================
// Watchers
// ============================================================================

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
            <CalendarMonthView
                v-if="viewMode === 'month'"
                :week-days="weekDays"
                :grid="grid"
                :entries="entries"
                :pending-date-changes="pendingDateChanges"
                :selected-date="selectedDate"
                :drag-over-target="dragOverTarget"
                :create-url="createUrl"
                :blueprints="blueprints"
                @select-date="selectDate"
                @entry-dragstart="handleEntryDragStart"
                @drag-over="handleDragOver"
                @drag-enter="handleDragEnter"
                @drag-leave="handleDragLeave"
                @drop="handleDrop"
            />

            <!-- Week View -->
            <CalendarWeekView
                v-else
                ref="weekViewRef"
                :week-dates="getWeekDates(currentDate)"
                :entries="entries"
                :pending-date-changes="pendingDateChanges"
                :selected-date="selectedDate"
                :drag-over-target="dragOverTarget"
                :create-url="createUrl"
                :blueprints="blueprints"
                :entries-by-hour="entriesByHour"
                @select-date="selectDate"
                @entry-dragstart="handleEntryDragStart"
                @drag-over="handleDragOver"
                @drag-enter="handleDragEnter"
                @drag-leave="handleDragLeave"
                @drop="handleDrop"
            />
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
