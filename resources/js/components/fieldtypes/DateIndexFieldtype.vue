<template>
    <div v-text="formatted"></div>
</template>

<script setup>
import Fieldtype from '@/components/fieldtypes/index-fieldtype.js';
import DateFormatter from '@/components/DateFormatter.js';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);

const formatted = computed(() => {
    if (!props.value) {
        return null;
    }

    const formatter = new DateFormatter().options(props.value.time_enabled ? 'datetime' : 'date');

    if (props.value.mode === 'range') {
        let start = new Date(props.value.start);
        let end = new Date(props.value.end);

        return formatter.date(start) + ' – ' + formatter.date(end);
    }

    return formatter.date(props.value.date).toString();
});

defineExpose(expose);
</script>
