<template>
    <TimePicker
        ref="time"
        :model-value="timePickerValue"
        :granularity="useSeconds ? 'second' : 'minute'"
        @update:model-value="timePickerUpdated"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { TimePicker } from '@statamic/ui';
import { parseTime } from '@internationalized/date';

export default {
    mixins: [Fieldtype],

    components: {
        TimePicker,
    },

    computed: {
        useSeconds() {
            return this.config.seconds_enabled;
        },

        timePickerValue() {
            return this.value ? parseTime(this.value) : null;
        },
    },

    methods: {
        focus() {
            this.$refs.time.focus();
        },

        timePickerUpdated(value) {
            if (!value) return this.update(null);

            value = value.toString();

            if (!this.secondsEnabled) {
                value = value.slice(0, 5);
            }

            this.update(value);
        },
    },
};
</script>
