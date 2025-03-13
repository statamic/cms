<template>
    <div v-text="formatted"></div>
</template>

<script>
import IndexFieldtype from './IndexFieldtype.vue';

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

                const formatter = Intl.DateTimeFormat(navigator.language, {
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                });

                return formatter.format(start) + ' – ' + formatter.format(end);
            }

            return Intl.DateTimeFormat(navigator.language, {
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
                ...(this.value.time_enabled && this.value.time ? { hour: 'numeric', minute: 'numeric' } : {}),
            }).format(new Date(this.value.date + 'T' + (this.value.time || '00:00:00') + 'Z'));
        },
    },
};
</script>
