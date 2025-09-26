<script setup>
import { ref, watch, computed } from 'vue';
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


function fetchEntries() {
    loading.value = true;

    const startOfMonth = new Date(currentDate.value.year, currentDate.value.month - 1, 1);
    const endOfMonth = new Date(currentDate.value.year, currentDate.value.month, 0);

    axios.get(cp_url(`collections/${props.collection}/entries`), {
        params: {
            date: `${startOfMonth.toISOString().split('T')[0]} to ${endOfMonth.toISOString().split('T')[0]}`,
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

function selectDate(date) {
    selectedDate.value = date;
}

function formatTime(dateString) {
    if (!dateString) return '';

    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
}

const selectedDateEntries = computed(() => {
    if (!selectedDate.value) return [];
    return getEntriesForDate(selectedDate.value);
});

const columns = computed(() => [
    { label: 'Title', field: 'title', visible: true },
    { label: 'Status', field: 'status', visible: true }
]);


watch(() => [currentDate.value.year, currentDate.value.month], fetchEntries, { immediate: true });
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
            <CalendarHeader class="flex flex-col @3xl:flex-row items-center justify-between gap-4 @3xl:mb-4 pb-4 @3xl:pb-8">
                <CalendarHeading class="text-lg font-normal text-gray-800 dark:text-white" />
                <div class="flex items-center gap-2">
                    <CalendarPrev as-child>
                        <ui-button icon="chevron-left" />
                    </CalendarPrev>
                    <ui-button @click="goToToday" :text="__('Today')" />
                    <CalendarNext as-child>
                        <ui-button icon="chevron-right" />
                    </CalendarNext>
                </div>
            </CalendarHeader>

            <CalendarGrid class="w-full border-collapse">
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
