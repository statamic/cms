<script setup>
import { computed } from 'vue';
import { dateFormatter, config } from '@api';
import { getLocalTimeZone } from '@internationalized/date';
import Text from './Text.vue';

const props = defineProps({
    /** The date to display across timezones. Accepts a `Date`, ISO string, epoch number, or a `{ start, end }` range object. */
    date: { type: [String, Date, Number, Object], required: true },
    /** Extra `{ timezone, label }` rows to render above the defaults. */
    additionalTimezones: { type: Array, default: () => [] },
});

const isRange = computed(() => {
    const value = props.date;
    return value !== null && typeof value === 'object' && !(value instanceof Date) && 'start' in value && 'end' in value;
});

const start = computed(() => new Date(isRange.value ? props.date.start : props.date));

const end = computed(() => isRange.value ? new Date(props.date.end) : null);

const isValid = computed(() => {
    if (isNaN(start.value.getTime())) return false;
    if (isRange.value && isNaN(end.value.getTime())) return false;
    return true;
});

const appTimezone = computed(() => config.get('appTimezone') ?? 'UTC');

const formatTimeZone = (timeZone) => {
    const parts = new Intl.DateTimeFormat(config.get('translationLocale'), {
        timeZone,
        timeZoneName: 'short',
    }).formatToParts(start.value);
    return parts.find((p) => p.type === 'timeZoneName')?.value ?? timeZone;
};

const formatDateTime = (timeZone) => {
    const formatter = new Intl.DateTimeFormat(dateFormatter.locale, {
        dateStyle: isRange.value ? 'short' : 'medium',
        timeStyle: 'medium',
        timeZone,
    });
    return isRange.value ? formatter.formatRange(start.value, end.value) : formatter.format(start.value);
};

const rows = computed(() => [
    ...props.additionalTimezones.map((row) => ({ timeZone: row.timezone, sublabel: row.label })),
    { timeZone: getLocalTimeZone(), sublabel: __('Your computer') },
    { timeZone: appTimezone.value, sublabel: __('Application') },
    { timeZone: 'UTC', sublabel: null },
]);
</script>

<template>
    <div v-if="isValid" class="grid grid-cols-[auto_auto_auto] gap-x-3 gap-y-1 text-sm">
        <template v-for="(row, index) in rows" :key="index">
            <Text :text="formatTimeZone(row.timeZone)" variant="strong" />
            <Text :text="row.sublabel" variant="subtle" />
            <Text :text="formatDateTime(row.timeZone)" />
        </template>
    </div>
</template>
