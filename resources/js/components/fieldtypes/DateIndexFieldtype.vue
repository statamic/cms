<template>
    <TimezoneHoverCard v-if="showTimezoneCard" :date="hoverCardDate" side="top" :offset="10">
        <span v-text="formatted"></span>
    </TimezoneHoverCard>
    <div v-else v-text="formatted"></div>
</template>

<script setup>
import Fieldtype from '@/components/fieldtypes/index-fieldtype.js';
import DateFormatter from '@/components/DateFormatter.js';
import { TimezoneHoverCard } from '@ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);

const timezoneOption = computed(() => {
    const tz = props.value?.timezone;

    return tz && tz !== 'auto' ? { timeZone: tz } : {};
});

const showTimeInValue = computed(() => props.value?.format_has_time && props.value?.time_enabled);

const showTimezoneCard = computed(() => props.value?.format_has_time);

const isRange = computed(() => props.value?.mode === 'range');

const formatter = computed(() => {
    const options = { preset: showTimeInValue.value ? 'datetime' : 'date' };
    if (!props.value?.format_has_time) {
        options.timeZone = 'UTC';
    } else {
        Object.assign(options, timezoneOption.value);
    }

    return new DateFormatter().options(options);
});

const formattedStart = computed(() => formatter.value.date(new Date(props.value.start)).toString());

const formattedEnd = computed(() => formatter.value.date(new Date(props.value.end)).toString());

const hoverCardDate = computed(() => isRange.value ? props.value : props.value.date);

const formatted = computed(() => {
    if (!props.value) {
        return null;
    }

    if (isRange.value) {
        return formattedStart.value + ' – ' + formattedEnd.value;
    }

    return formatter.value.date(props.value.date).toString();
});

defineExpose(expose);
</script>
