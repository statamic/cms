<template>
    <div class="datetime min-w-[145px]">
        <Button :text="__('Add Date')" icon="calendar" v-if="!isReadOnly && !isInline && !hasDate" @click="addDate" />

        <Component
            v-if="hasDate || isInline"
            :disabled="config.disabled"
            :granularity="datePickerGranularity"
            :inline="isInline"
            :is="pickerComponent"
            :max="config.latest_date"
            :min="config.earliest_date"
            :model-value="datePickerValue"
            :number-of-months="config.number_of_months"
            :read-only="isReadOnly"
            :clearable="config.clearable"
            @update:model-value="datePickerUpdated"
        />
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import DateFormatter from '@/components/DateFormatter.js';
import { DatePicker, DateRangePicker, Button } from '@/components/ui';
import { CalendarDate, getLocalTimeZone, parseAbsoluteToLocal, toTimeZone, toZoned } from '@internationalized/date';

export default {
    components: {
        DatePicker,
        DateRangePicker,
        Button,
    },

    mixins: [Fieldtype],

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
            return this.config.required || (this.value && this.value !== 'now');
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

        formatHasTime() {
            return this.meta?.formatHasTime ?? true;
        },

        datePickerValue() {
            if (!this.value || this.value === 'now') {
                return null;
            }

            if (!this.formatHasTime) {
                if (this.isRange) {
                    return {
                        start: this.parseDateOnly(this.value.start),
                        end: this.parseDateOnly(this.value.end),
                    };
                }

                return this.parseDateOnly(this.value);
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
            if (!this.showFieldPreviews) return;
            if (!this.value) return;

            if (this.isRange) {
                const formatter = new DateFormatter().options(this.hasTime ? 'datetime' : 'date');
                return formatter.date(this.value.start) + ' – ' + formatter.date(this.value.end);
            }

            return DateFormatter.format(this.value, this.hasTime && this.value ? 'datetime' : 'date');
        },
    },

    created() {
        this.$events.$on(`container.${this.publishContainer.name}.saving`, this.triggerChangeOnFocusedField);

        if (this.value === 'now') {
            this.injectedPublishContainer.withoutDirtying(() => this.addDate());
        }
    },

    unmounted() {
        this.$events.$off(`container.${this.publishContainer.name}.saving`, this.triggerChangeOnFocusedField);
    },

    methods: {
        triggerChangeOnFocusedField() {
            if (!this.focusedField) return;

            this.focusedField.dispatchEvent(new Event('change'));
        },

        datePickerUpdated(value) {
	        // Clearing the date on a required Date field should set the date/time to now.
	        if (!value && !this.isRange && this.config.required) {
				return this.addDate();
	        }

            if (!value) {
                return this.update(null);
            }

            if (!this.formatHasTime) {
                if (this.isRange) {
                    return this.update({
                        start: this.formatDateOnly(value.start),
                        end: this.formatDateOnly(value.end),
                    });
                }

                return this.update(this.formatDateOnly(value));
            }

            // Sometimes, we'll get a CalendarDateTime object, which doesn't include timezone
            // information. In that case, we need to convert it to a ZonedDateTime object.
            if (!this.isRange && !value.offset && !value.timeZone) {
                value = toZoned(value, getLocalTimeZone());
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

            if (!this.formatHasTime) {
                const str = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
                return this.update(this.isRange ? { start: str, end: str } : str);
            }

            now.setMilliseconds(0);

            if (!this.config.time_enabled) {
                now.setHours(0, 0, 0, 0);
            }

            const str = now.toISOString();

            this.update(this.isRange ? { start: str, end: str } : str);
        },

        parseDateOnly(value) {
            const [year, month, day] = value.split('-').map(Number);
            return new CalendarDate(year, month, day);
        },

        formatDateOnly(value) {
            return `${value.year}-${String(value.month).padStart(2, '0')}-${String(value.day).padStart(2, '0')}`;
        },
    },
};
</script>
