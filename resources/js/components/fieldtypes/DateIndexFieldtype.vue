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

const showTimeInValue = computed(() => props.value?.format_has_time && props.value?.time_enabled);

const showTooltip = computed(() => props.value?.format_has_time);

const formatted = computed(() => {
    if (!props.value) {
        return null;
    }

    const preset = showTimeInValue.value ? 'datetime' : 'date';
    const formatter = new DateFormatter().options({ preset, ...timezoneOption.value });

    if (props.value.mode === 'range') {
        let start = new Date(props.value.start);
        let end = new Date(props.value.end);

        return formatter.date(start) + ' – ' + formatter.date(end);
    }

    return formatter.date(props.value.date).toString();
});

const tooltip = computed(() => {
    if (!props.value || !showTooltip.value) {
        return null;
    }

    const formatter = new DateFormatter().options({ dateStyle: 'long', timeStyle: 'long', ...timezoneOption.value });

    if (props.value.mode === 'range') {
        let start = new Date(props.value.start);
        let end = new Date(props.value.end);

        return formatter.date(start) + ' – ' + formatter.date(end);
    }

    return formatter.date(props.value.date).toString();
});

defineExpose(expose);
</script>
