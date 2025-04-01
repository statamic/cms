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

            const formatter = new DateFormatter().options(this.value.time_enabled ? 'datetime' : 'date');

            if (this.value.mode === 'range') {
                let start = new Date(this.value.start);
                let end = new Date(this.value.end);

                return formatter.date(start) + ' – ' + formatter.date(end);
            }

            return formatter.date(this.value.date);
        },
    },
};
</script>
