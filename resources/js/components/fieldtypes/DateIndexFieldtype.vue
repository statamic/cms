<template>
    <div v-text="formatted" v-tooltip="tooltip"></div>
</template>

<script setup>
import Fieldtype from '@/components/fieldtypes/index-fieldtype.js';
import DateFormatter from '@/components/DateFormatter.js';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);

const timezoneOption = computed(() => {
    const tz = props.value?.timezone;

    return tz && tz !== 'auto' ? { timeZone: tz } : {};
});

const formatted = computed(() => {
    if (!props.value) {
        return null;
    }

    const preset = props.value.time_enabled ? 'datetime' : 'date';
    const formatter = new DateFormatter().options({ ...DateFormatter.presets[preset], ...timezoneOption.value });

    if (props.value.mode === 'range') {
        let start = new Date(props.value.start);
        let end = new Date(props.value.end);

        return formatter.date(start) + ' – ' + formatter.date(end);
    }

    return formatter.date(props.value.date).toString();
});

const tooltip = computed(() => {
    if (!props.value) {
        return null;
    }

    const tooltipOptions = props.value.time_enabled
        ? { dateStyle: 'long', timeStyle: 'long' }
        : { dateStyle: 'long' };

    const formatter = new DateFormatter().options({ ...tooltipOptions, ...timezoneOption.value });

    if (props.value.mode === 'range') {
        let start = new Date(props.value.start);
        let end = new Date(props.value.end);

        return formatter.date(start) + ' – ' + formatter.date(end);
    }

    return formatter.date(props.value.date).toString();
});

defineExpose(expose);
</script>
