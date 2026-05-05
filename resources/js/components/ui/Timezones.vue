<script setup>
import { computed } from 'vue';
import { config } from '@api';
import { getLocalTimeZone } from '@internationalized/date';
import Heading from './Heading.vue';

const props = defineProps({
    /** The date to display across timezones. Accepts a `Date`, ISO string, or epoch number. */
    date: { type: [String, Date, Number], required: true },
});

const normalizedDate = computed(() => new Date(props.date));

const locale = computed(() => config.get('translationLocale'));

const appTimezone = computed(() => {
    try {
        return window.Statamic?.$config?.get('appTimezone') ?? 'UTC';
    } catch (e) {
        return 'UTC';
    }
});

const formatTimeZone = (timeZone) => {
    try {
        const parts = new Intl.DateTimeFormat(locale.value, {
            timeZone,
            timeZoneName: 'short',
        }).formatToParts(normalizedDate.value);
        return parts.find((p) => p.type === 'timeZoneName')?.value ?? timeZone;
    } catch (e) {
        return timeZone;
    }
};

const formatTime = (timeZone) => {
    try {
        return new Intl.DateTimeFormat(locale.value, { timeStyle: 'medium', timeZone }).format(normalizedDate.value);
    } catch (e) {
        return '';
    }
};

const formatDate = (timeZone) => {
    try {
        return new Intl.DateTimeFormat(locale.value, { dateStyle: 'long', timeZone }).format(normalizedDate.value);
    } catch (e) {
        return '';
    }
};

const rows = computed(() => [
    { timeZone: getLocalTimeZone(), sublabel: __('Your computer') },
    { timeZone: appTimezone.value, sublabel: __('App timezone') },
    { timeZone: 'UTC', sublabel: null },
]);
</script>

<template>
    <div class="bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-x-0 dark:ring-b-0 dark:ring-gray-700/80 shadow-ui-md px-4 sm:px-4.5 py-5 space-y-2">
        <Heading :text="__('Time conversion')" />
        <div class="grid grid-cols-[auto_auto_auto_auto] gap-x-3 gap-y-1 text-sm">
            <template v-for="(row, index) in rows" :key="index">
                <div class="font-mono text-gray-500 dark:text-gray-400">{{ formatTimeZone(row.timeZone) }}</div>
                <div class="text-gray-500 dark:text-gray-400">{{ row.sublabel }}</div>
                <div>{{ formatTime(row.timeZone) }}</div>
                <div class="text-gray-500 dark:text-gray-400">{{ formatDate(row.timeZone) }}</div>
            </template>
        </div>
    </div>
</template>
