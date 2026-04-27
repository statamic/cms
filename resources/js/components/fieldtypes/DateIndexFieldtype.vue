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

const datePresets = {
    datetime: { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric' },
    date: { year: 'numeric', month: 'numeric', day: 'numeric' },
};

const timezoneOption = computed(() => {
    const tz = props.value?.timezone;

    return tz && tz !== 'auto' ? { timeZone: tz } : {};
});

const formatted = computed(() => {
    if (!props.value) {
        return null;
    }

    const preset = props.value.time_enabled ? 'datetime' : 'date';
    const formatter = new DateFormatter().options({ ...datePresets[preset], ...timezoneOption.value });

    if (props.value.mode === 'range') {
        return formatter.date(new Date(props.value.start)) + ' – ' + formatter.date(new Date(props.value.end));
    }

    return formatter.date(props.value.date).toString();
});

const tooltip = computed(() => {
    if (!props.value) {
        return null;
    }

    const formatter = new DateFormatter().options({ dateStyle: 'long', timeStyle: 'long', ...timezoneOption.value });

    if (props.value.mode === 'range') {
        return formatter.date(new Date(props.value.start)) + ' – ' + formatter.date(new Date(props.value.end));
    }

    return formatter.date(props.value.date).toString();
});

defineExpose(expose);
</script>
