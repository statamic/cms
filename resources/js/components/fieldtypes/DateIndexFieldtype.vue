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
                return (
                    this.$moment(this.value.start.date + 'T' + (this.value.start.time || '00:00:00') + 'Z').format(this.value.display_format) +
                    ' – ' +
                    this.$moment(this.value.end.date + 'T' + (this.value.end.time || '00:00:00') + 'Z').format(this.value.display_format)
                );
            }

            return this.$moment(this.value.date + 'T' + (this.value.time || '00:00:00') + 'Z').format(this.value.display_format);
        },
    },
};
</script>
