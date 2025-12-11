<template>
	<Button :text="__('Set Time')" icon="fieldtype-time" v-if="!isReadOnly && !hasTime" @click="addTime" />

    <TimePicker
	    v-if="hasTime"
        ref="time"
        :model-value="timePickerValue"
        :granularity="useSeconds ? 'second' : 'minute'"
        @update:model-value="timePickerUpdated"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import {Button, TimePicker} from '@/components/ui';
import { parseTime } from '@internationalized/date';

export default {
    mixins: [Fieldtype],

    components: {
	    Button,
        TimePicker,
    },

    computed: {
	    hasTime() {
		    return !!(this.config.required || this.value);
	    },

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

            if (!this.useSeconds) {
                value = value.slice(0, 5);
            }

            this.update(value);
        },

	    addTime() {
		    const date = new Date();
		    const hours = String(date.getHours()).padStart(2, '0');
		    const minutes = String(date.getMinutes()).padStart(2, '0');
		    const seconds = String(date.getSeconds()).padStart(2, '0');

		    this.update(this.useSeconds ? `${hours}:${minutes}:${seconds}` : `${hours}:${minutes}`);
	    },
    },
};
</script>
