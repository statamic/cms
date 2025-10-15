<script setup>
import { ref, watch, computed, getCurrentInstance } from 'vue';
import axios from 'axios';
import { CalendarHeader, CalendarHeading, CalendarRoot } from 'reka-ui';
import { CalendarDate } from '@internationalized/date';
import Month from './Month.vue';
import Week from './Week.vue';
import { Listing, StatusIndicator } from '@/components/ui';
import DateFormatter from '@/components/DateFormatter.js';
import { formatDateString, getWeekDates, getCurrentDateRange } from './calendar.js';
import { Link } from '@inertiajs/vue3';
import { ToggleGroup, ToggleItem, Button, Popover, Label, Select, Heading } from '@ui';
import { preferences } from '@api';

const props = defineProps({
    collection: { type: String, required: true },
    blueprints: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
    site: { type: String, required: true },
});

const $date = new DateFormatter;
const currentDate = ref(new CalendarDate(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate()));
const selectedDate = ref(null);
const entries = ref([]);
const loading = ref(false);
const error = ref(null);
const datePickerOpen = ref(false);

const viewModePreferenceKey = `collections.${props.collection}.calendar.view`;
const viewMode = ref(preferences.get(viewModePreferenceKey, 'month')); // 'month' or 'week'
watch(viewMode, (viewMode) => preferences.set(viewModePreferenceKey, viewMode));

async function fetchEntries() {
    loading.value = true;
    error.value = null;

    try {
        const { startDate, endDate } = getCurrentDateRange(currentDate.value, viewMode.value);

        const response = await axios.get(cp_url(`collections/${props.collection}/entries`), {
            params: {
                filters: utf8btoa(JSON.stringify({
                    site: {
                        site: props.site,
                    },
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
    const months = [];
    for (let i = 1; i <= 12; i++) {
        const date = new Date(2024, i - 1, 1);
        months.push({
            value: i,
            label: $date.format(date, { month: 'long' })
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

    const dateStr = formatDateString(selectedDate.value);

    return entries.value.filter(entry => {
        const entryDate = new Date(entry.date?.date || entry.date);
        return entryDate.toISOString().split('T')[0] === dateStr;
    });
});

const columns = computed(() => [
    { label: 'Title', field: 'title', visible: true },
    { label: 'Status', field: 'status', visible: true }
]);

watch(() => props.site, () => fetchEntries());

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
                    <ToggleGroup v-model="viewMode" class="flex">
                        <ToggleItem value="week" :label="__('Week')" />
                        <ToggleItem value="month" :label="__('Month')" />
                    </ToggleGroup>

                    <div class="flex items-center gap-2 @3xl:hidden">
                        <Button icon="chevron-left" @click="goToPreviousPeriod" />
                        <Button @click="goToToday" :text="__('Today')" />
                        <Button icon="chevron-right" @click="goToNextPeriod" />
                    </div>
                </div>

                <!-- Calendar Heading with Popover -->
                <div class="@3xl:flex-1 px-2 text-center">
                    <Popover v-model:open="datePickerOpen" class="w-full" arrow>
                        <template #trigger>
                            <button @click="datePickerOpen = true">
                                <CalendarHeading
                                    class="text-2xl font-medium text-gray-800 dark:text-white cursor-pointer hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                />
                            </button>
                        </template>

                        <div class="flex items-center gap-3">
                            <div class="space-y-2">
                                <Label for="month">{{ __('Month') }}</Label>
                                <Select
                                    :model-value="currentDate.month"
                                    :options="monthOptions"
                                    option-value="value"
                                    option-label="label"
                                    @update:modelValue="handleMonthChange"
                                />
                            </div>
                            <div class="space-y-2">
                                <Label for="month">{{ __('Year') }}</Label>
                                <Select
                                    :model-value="currentDate.year"
                                    :options="yearOptions"
                                    option-value="value"
                                    option-label="label"
                                    @update:modelValue="handleYearChange"
                                />
                            </div>
                        </div>
                    </Popover>
                </div>

                <div class="hidden @3xl:flex @3xl:flex-1 items-center gap-2 w-1/4 justify-end">
                    <Button icon="chevron-left" @click="goToPreviousPeriod" />
                    <Button @click="goToToday" :text="__('Today')" />
                    <Button icon="chevron-right" @click="goToNextPeriod" />
                </div>
            </CalendarHeader>

            <Month
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
            <Week
                v-else
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
            <Heading
                size="lg"
                class="flex justify-center pb-4"
                :text="$date.format(selectedDate, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"
            />

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
