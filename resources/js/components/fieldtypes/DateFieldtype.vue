<template>
    <div class="datetime min-w-[145px]">
        <Button :text="__('Add Date')" icon="calendar" v-if="!isReadOnly && !isInline && !hasDate" @click="addDate" />

        <Component
            :is="pickerComponent"
            v-if="hasDate || isInline"
            :model-value="datePickerValue"
            :granularity="datePickerGranularity"
            :inline="isInline"
            @update:model-value="datePickerUpdated"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import DateFormatter from '@statamic/components/DateFormatter.js';
import Button from '@statamic/components/ui/Button/Index.vue';
import { DatePicker, DateRangePicker } from '@statamic/ui';
import { parseAbsoluteToLocal, toTimeZone, toZoned } from '@internationalized/date';

export default {
    components: {
        DatePicker,
        DateRangePicker,
        Button,
    },

    mixins: [Fieldtype],

    inject: ['store'],

    data() {
        return {
            containerWidth: null,
            focusedField: null,
            localValue: null,
        };
    },

    computed: {
        pickerComponent() {
            return this.isRange ? DateRangePicker : DatePicker;
        },

        hasDate() {
            return !!(this.config.required || this.value);
        },

        hasTime() {
            return this.config.time_enabled;
        },

        hasSeconds() {
            return this.config.time_seconds_enabled;
        },

        isRange() {
            return this.config.mode === 'range';
        },

        isInline() {
            return this.config.inline;
        },

        datePickerValue() {
            if (!this.value) {
                return null;
            }

            if (this.isRange) {
                return {
                    start: parseAbsoluteToLocal(this.value.start),
                    end: parseAbsoluteToLocal(this.value.end),
                };
            }

            return parseAbsoluteToLocal(this.value);
        },

        datePickerGranularity() {
            return this.hasTime ? (this.hasSeconds ? 'second' : 'minute') : 'day';
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            if (!this.value) return;

            if (this.isRange) {
                const formatter = new DateFormatter().options(this.hasTime ? 'datetime' : 'date');
                return formatter.date(this.value.start) + ' – ' + formatter.date(this.value.end);
            }

            return DateFormatter.format(this.value, this.hasTime && this.value ? 'datetime' : 'date');
        },
    },

    created() {
        this.$events.$on(`container.${this.storeName}.saving`, this.triggerChangeOnFocusedField);
    },

    unmounted() {
        this.$events.$off(`container.${this.storeName}.saving`, this.triggerChangeOnFocusedField);
    },

    methods: {
        triggerChangeOnFocusedField() {
            if (!this.focusedField) return;

            this.focusedField.dispatchEvent(new Event('change'));
        },

        datePickerUpdated(value) {
            if (!value) {
                return this.update(null);
            }

            // The date picker will give us CalendarDateTimes in the local time zone.
            // We want them in UTC.

            if (this.isRange) {
                let start = value.start;
                let end = value.end;

                if (!this.hasTime) {
                    end.set({ hour: 23, minute: 59, second: 59 });
                }

                return this.update({
                    start: toZoned(start, 'UTC').toAbsoluteString(),
                    end: toZoned(end, 'UTC').toAbsoluteString(),
                });
            }

            return this.update(toTimeZone(value, 'UTC').toAbsoluteString());
        },

        addDate() {
            let now = new Date();

            now.setMilliseconds(0);

            if (!this.config.time_enabled) {
                now.setHours(0, 0, 0, 0);
            }

            const str = now.toISOString();

            this.update(this.isRange ? { start: str, end: str } : str);
        },
    },
};
</script>
