<script setup>
import { ref, watch, computed, nextTick, getCurrentInstance } from 'vue';
import axios from 'axios';
import { CalendarHeader, CalendarHeading, CalendarRoot } from 'reka-ui';
import { CalendarDate } from '@internationalized/date';
import CalendarMonthView from './CalendarMonthView.vue';
import CalendarWeekView from './CalendarWeekView.vue';
import { Listing, StatusIndicator } from '@/components/ui';
import { formatDateString, getWeekDates, getVisibleHours, getCurrentDateRange } from '@/util/calendar.js';
import { Link } from '@inertiajs/vue3';

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
const datePickerOpen = ref(false);

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
                filters: utf8btoa(JSON.stringify({
                    fields: {
                        date: {
                            operator: 'between',
                            range_value: {
                                start: startDate.toISOString(),
                                end: endDate.toISOString(),
                            }
                        }
                    }
                })),
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
    const period = viewMode.value === 'week' ? 'weeks' : 'months';
    currentDate.value = currentDate.value.subtract({ [period]: 1 });
}

function goToNextPeriod() {
    const period = viewMode.value === 'week' ? 'weeks' : 'months';
    currentDate.value = currentDate.value.add({ [period]: 1 });
}

function selectDate(date) {
    selectedDate.value = date;
}

function handleMonthChange(newMonth) {
    currentDate.value = new CalendarDate(currentDate.value.year, newMonth, currentDate.value.day);
}

function handleYearChange(newYear) {
    currentDate.value = new CalendarDate(newYear, currentDate.value.month, currentDate.value.day);
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
// Month and Year Options
// ============================================================================

const monthOptions = computed(() => {
    const instance = getCurrentInstance();
    const $date = instance?.appContext.config.globalProperties.$date;
    const months = [];
    for (let i = 1; i <= 12; i++) {
        const date = new Date(2024, i - 1, 1);
        months.push({
            value: i,
            label: date.toLocaleDateString($date?.locale || 'en', { month: 'long' })
        });
    }
    return months;
});

const yearOptions = computed(() => {
    const currentYear = new Date().getFullYear();
    const years = [];
    for (let i = currentYear - 10; i <= currentYear + 10; i++) {
        years.push({
            value: i,
            label: i.toString()
        });
    }
    return years;
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

// ============================================================================
// Watchers
// ============================================================================

watch(() => [currentDate.value.year, currentDate.value.month, currentDate.value.day, viewMode.value], fetchEntries, { immediate: true });

</script>

<template>
    <div class="@container">
        <CalendarRoot
            v-model="currentDate"
            :locale="$date.locale"
            fixed-weeks
            v-slot="{ weekDays, grid }"
            weekday-format="long"
            class="bg-gray-100 dark:bg-gray-800 rounded-2xl p-3"
        >
            {{ currentDate }}
            <CalendarHeader class="flex flex-col @3xl:flex-row items-center gap-4 pb-4 @3xl:pb-8">
                <div class="flex items-center justify-between w-full @3xl:flex-1 @3xl:justify-start">
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

                <!-- Calendar Heading with Popover -->
                <div class="@3xl:flex-1 px-2 text-center">
                    <ui-popover v-model:open="datePickerOpen" class="w-full" arrow>
                        <template #trigger>
                            <button @click="datePickerOpen = true">
                                <CalendarHeading
                                    class="text-2xl font-medium text-gray-800 dark:text-white cursor-pointer hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                />
                            </button>
                        </template>

                        <div class="flex items-center gap-3">
                            <div class="space-y-2">
                                <ui-label for="month">{{ __('Month') }}</ui-label>
                                <ui-select
                                    :model-value="currentDate.month"
                                    :options="monthOptions"
                                    option-value="value"
                                    option-label="label"
                                    @update:modelValue="handleMonthChange"
                                />
                            </div>
                            <div class="space-y-2">
                                <ui-label for="month">{{ __('Year') }}</ui-label>
                                <ui-select
                                    :model-value="currentDate.year"
                                    :options="yearOptions"
                                    option-value="value"
                                    option-label="label"
                                    @update:modelValue="handleYearChange"
                                />
                            </div>
                        </div>
                    </ui-popover>
                </div>

                <div class="hidden @3xl:flex @3xl:flex-1 items-center gap-2 w-1/4 justify-end">
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
        <div class="mt-6" v-if="selectedDate">
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
                    <Link :href="entry.edit_url" v-text="entry.title" />
                </template>
                <template #cell-status="{ row: entry }">
                    <StatusIndicator :status="entry.status" :show-dot="false" show-label />
                </template>
            </Listing>
        </div>
    </div>
</template>
