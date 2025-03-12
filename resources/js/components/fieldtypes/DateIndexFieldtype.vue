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
                let start = new Date(this.value.start.date + 'T' + (this.value.start.time || '00:00:00') + 'Z').toLocaleDateString(
                    navigator.language,
                    {
                        year: 'numeric',
                        month: 'numeric',
                        day: 'numeric'
                    }
                );

                let end = new Date(this.value.end.date + 'T' + (this.value.end.time || '00:00:00') + 'Z').toLocaleDateString(
                    navigator.language,
                    {
                        year: 'numeric',
                        month: 'numeric',
                        day: 'numeric'
                    }
                );

                return `${start} – ${end}`;
            }

            return new Date(this.value.date + 'T' + (this.value.time || '00:00:00') + 'Z').toLocaleDateString(
                navigator.language,
                {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }
            );
        },
    },
};
</script>
