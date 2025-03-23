<template>
    <div v-text="formatted"></div>
</template>

<script>
import IndexFieldtype from './IndexFieldtype.vue';
import DateFormatter from '@statamic/components/DateFormatter.js';

export default {
    mixins: [IndexFieldtype],

    computed: {
        formatted() {
            if (!this.value) {
                return null;
            }

            if (this.value.mode === 'range') {
                let start = new Date(this.value.start.date + 'T' + (this.value.start.time || '00:00:00') + 'Z');
                let end = new Date(this.value.end.date + 'T' + (this.value.end.time || '00:00:00') + 'Z');
                const formatter = new DateFormatter().options('date');

                return formatter.date(start) + ' – ' + formatter.date(end);
            }

            return DateFormatter.format(
                this.value.date + 'T' + (this.value.time || '00:00:00') + 'Z',
                this.value.time_enabled && this.value.time ? 'datetime' : 'date',
            );
        },
    },
};
</script>
