<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import { CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody, CalendarGridHead, CalendarGridRow, CalendarHeadCell, CalendarHeader, CalendarHeading, CalendarRoot, CalendarPrev, CalendarNext } from 'reka-ui';
import { CalendarDate } from '@internationalized/date';
import { Icon } from '@ui';
import CreateEntryButton from './CreateEntryButton.vue';

const props = defineProps({
    collection: { type: String, required: true },
    blueprints: { type: Array, default: () => [] },
    createUrl: { type: String, required: true },
});

const currentDate = ref(new CalendarDate(new Date().getFullYear(), new Date().getMonth() + 1, new Date().getDate()));
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

function formatTime(dateString) {
    if (!dateString) return '';

    const date = new Date(dateString);
    return date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

watch(() => [currentDate.value.year, currentDate.value.month], fetchEntries, { immediate: true });

onMounted(() => {
    fetchEntries();
});
</script>

<template>
    <CalendarRoot
        v-model="currentDate"
        :locale="$date.locale"
        fixed-weeks
        v-slot="{ weekDays, grid }"
        weekday-format="long"
        class="bg-gray-100 dark:bg-gray-900 rounded-2xl p-3 lg:p-6"
    >
        <CalendarHeader class="flex items-center justify-between mb-4 pb-8">
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
                        {{ day }}
                    </CalendarHeadCell>
                </CalendarGridRow>
            </CalendarGridHead>

            <CalendarGridBody class="space-y-4">
                <template v-for="month in grid" :key="month.value.toString()">
                    <CalendarGridRow
                        v-for="(weekDates, weekIndex) in month.rows"
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
                                class="w-full h-full flex flex-col"
                                v-slot="{ dayValue, selected, today, outsideView }"
                            >
                                <!-- Date number -->
                                <div
                                    class="text-sm mb-1"
                                    v-text="dayValue"
                                    :class="{
                                        'text-gray-400 dark:text-gray-600': outsideView,
                                        'text-gray-900 dark:text-white': !outsideView,
                                        // 'bg-blue-600 text-white rounded-full size-6 flex items-center justify-center': selected,
                                        'bg-ui-accent text-white rounded-full size-6 flex items-center justify-center': today
                                    }"
                                />

                                <!-- Entries -->
                                <div class="space-y-1.5 flex-1 overflow-scroll h-full">
                                    <a
                                        :href="entry.edit_url"
                                        :key="entry.id"
                                        class="text-xs px-2 block line-clamp-2 border-s-2 rounded-e-sm cursor-pointer"
                                        :class="{
                                            'border-green-500 hover:bg-green-50': entry.status === 'published',
                                            'border-gray-300 hover:bg-gray-50': entry.status === 'draft',
                                            'border-purple-500 hover:bg-purple-50': entry.status === 'scheduled'
                                        }"
                                        v-for="entry in getEntriesForDate(weekDate).slice(0, 3)"
                                    >
                                        {{ entry.title }}<br>
                                        <span class="text-2xs text-gray-400 dark:text-gray-400">
                                            {{ formatTime(entry.date?.date || entry.date) }}
                                        </span>
                                    </a>
                                    <div v-if="getEntriesForDate(weekDate).length > 5" class="text-xs text-gray-500 dark:text-gray-400">
                                        +{{ getEntriesForDate(weekDate).length - 3 }} {{ __('more') }}
                                    </div>
                                </div>
                            </CalendarCellTrigger>

                            <!-- Create entry button (shows on hover) -->
                            <div class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
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
</template>
