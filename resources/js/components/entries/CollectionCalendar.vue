<script setup>
import { ref, watch, computed, getCurrentInstance } from 'vue';
import axios from 'axios';
import { CalendarHeader, CalendarHeading, CalendarRoot } from 'reka-ui';
import { CalendarDate } from '@internationalized/date';
import CalendarMonthView from './CalendarMonthView.vue';
import CalendarWeekView from './CalendarWeekView.vue';
import { Listing, StatusIndicator } from '@/components/ui';
import { formatDateString, getWeekDates, getCurrentDateRange } from '@/util/calendar.js';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    collection: { type: String, required: true },
    blueprints: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
});

const currentDate = ref(new CalendarDate(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate()));
const selectedDate = ref(null);
const entries = ref([]);
const loading = ref(false);
const error = ref(null);
const viewMode = ref('month'); // 'month' or 'week'
const weekViewRef = ref(null);
const datePickerOpen = ref(false);

async function fetchEntries() {
    loading.value = true;
    error.value = null;

    try {
        const { startDate, endDate } = getCurrentDateRange(currentDate.value, viewMode.value);

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
        const entryDate = new Date(entry.date?.date || entry.date);
        return entryDate.toISOString().split('T')[0] === dateStr;
    });
}

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

const selectedDateEntries = computed(() => {
    if (!selectedDate.value) return [];
    return getEntriesForDate(selectedDate.value);
});

const columns = computed(() => [
    { label: 'Title', field: 'title', visible: true },
    { label: 'Status', field: 'status', visible: true }
]);

watch(
    () => [currentDate.value.year, currentDate.value.month, currentDate.value.day, viewMode.value],
    (newValue, oldValue) => {
        if (!oldValue || shouldFetchEntries(newValue, oldValue)) fetchEntries();
    },
    { deep: true, immediate: true }
);

function shouldFetchEntries(
    [year, month, day, view],
    [oldYear, oldMonth, oldDay, oldView]
) {
    if (view !== oldView) return true;

    if (view === 'month' && (year !== oldYear || month !== oldMonth)) return true;

    if (view === 'week') {
        const newDate = new CalendarDate(year, month, day);
        const oldDate = new CalendarDate(oldYear, oldMonth, oldDay);
        const newWeekStart = getWeekDates(newDate)[0];
        const oldWeekStart = getWeekDates(oldDate)[0];
        return newWeekStart.toString() !== oldWeekStart.toString();
    }

    return false;
}
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
                :selected-date="selectedDate"
                :create-url="createUrl"
                :blueprints="blueprints"
                @select-date="selectDate"
            />

            <!-- Week View -->
            <CalendarWeekView
                v-else
                ref="weekViewRef"
                :week-dates="getWeekDates(currentDate)"
                :entries="entries"
                :selected-date="selectedDate"
                :create-url="createUrl"
                :blueprints="blueprints"
                @select-date="selectDate"
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
