<script setup>
import { computed } from 'vue';
import { dateFormatter, config } from '@api';
import { getLocalTimeZone } from '@internationalized/date';
import Text from './Text.vue';

const props = defineProps({
    /** The date to display across timezones. Accepts a `Date`, ISO string, or epoch number. */
    date: { type: [String, Date, Number], required: true },
});

const normalizedDate = computed(() => new Date(props.date));

const appTimezone = computed(() => {
    try {
        return config.get('appTimezone') ?? 'UTC';
    } catch (e) {
        return 'UTC';
    }
});

const formatTimeZone = (timeZone) => {
    try {
        const parts = new Intl.DateTimeFormat(config.get('translationLocale'), {
            timeZone,
            timeZoneName: 'short',
        }).formatToParts(normalizedDate.value);
        return parts.find((p) => p.type === 'timeZoneName')?.value ?? timeZone;
    } catch (e) {
        return timeZone;
    }
};

const formatDateTime = (timeZone) => {
    try {
        return new Intl.DateTimeFormat(dateFormatter.locale, { dateStyle: 'long', timeStyle: 'medium', timeZone }).format(normalizedDate.value);
    } catch (e) {
        return '';
    }
};

const rows = computed(() => [
    { timeZone: getLocalTimeZone(), sublabel: __('Your computer') },
    { timeZone: appTimezone.value, sublabel: __('Application') },
    { timeZone: 'UTC', sublabel: null },
]);
</script>

<template>
    <div class="grid grid-cols-[auto_auto] gap-x-3 gap-y-1 text-sm">
        <template v-for="(row, index) in rows" :key="index">
            <div class="flex items-center gap-4">
                <Text :text="formatTimeZone(row.timeZone)" variant="strong" />
                <Text :text="row.sublabel" variant="subtle" />
            </div>
            <Text :text="formatDateTime(row.timeZone)" />
        </template>
    </div>
</template>
