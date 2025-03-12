<template>
    <div class="datetime min-w-[145px]">
        <button
            type="button"
            class="btn mb-2 flex items-center md:mb-0 ltr:pl-3 rtl:pr-3"
            v-if="!isReadOnly && config.inline === false && !hasDate"
            @click="addDate"
            tabindex="0"
        >
            <svg-icon name="light/calendar" class="h-4 w-4 ltr:mr-2 rtl:ml-2"></svg-icon>
            {{ __('Add Date') }}
        </button>

        <div v-if="hasDate || config.inline" class="date-time-container flex flex-col gap-2 @sm:flex-row">
            <component
                :is="pickerComponent"
                v-bind="pickerProps"
                @update:model-value="setLocalDate"
                @focus="focusedField = $event"
                @blur="focusedField = null"
            />

            <div v-if="config.time_enabled && !isRange" class="time-container time-fieldtype">
                <time-fieldtype
                    v-if="hasTime"
                    ref="time"
                    handle=""
                    :value="localValue.time"
                    :required="config.time_enabled"
                    :show-seconds="config.time_seconds_enabled"
                    :read-only="isReadOnly"
                    :config="{}"
                    @update:value="setLocalTime"
                />
            </div>
        </div>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import SinglePopover from './date/SinglePopover.vue';
import SingleInline from './date/SingleInline.vue';
import RangePopover from './date/RangePopover.vue';
import RangeInline from './date/RangeInline.vue';
import { useScreens } from 'vue-screen-utils';
import { toRaw } from 'vue';

export default {
    components: {
        SinglePopover,
        SingleInline,
        RangePopover,
        RangeInline,
    },

    mixins: [Fieldtype],

    inject: ['store'],

    setup() {
        const { mapCurrent } = useScreens({
            xs: '0px',
            sm: '640px',
            md: '768px',
            lg: '1024px',
        });

        return { screens: mapCurrent };
    },

    data() {
        return {
            containerWidth: null,
            focusedField: null,
            localValue: null,
        };
    },

    computed: {
        pickerComponent() {
            if (this.isRange) {
                return this.usesPopover ? 'RangePopover' : 'RangeInline';
            }

            return this.usesPopover ? 'SinglePopover' : 'SingleInline';
        },

        hasDate() {
            if (this.isRange) {
                return this.config.required || this.value?.start || this.value?.end;
            }

            return this.config.required || this.value.date;
        },

        hasTime() {
            return this.config.time_enabled && !this.isRange;
        },

        hasSeconds() {
            return this.config.time_seconds_enabled;
        },

        isSingle() {
            return !this.isRange;
        },

        isRange() {
            return this.config.mode === 'range';
        },

        isInline() {
            return this.config.inline;
        },

        usesPopover() {
            return !this.isInline;
        },

        pickerProps() {
            return {
                isReadOnly: this.isReadOnly,
                bindings: this.commonDatePickerBindings,
            };
        },

        datePickerValue() {
            if (this.isRange) {
                return {
                    start: this.localValue?.start?.date,
                    end: this.localValue?.end?.date,
                };
            }

            // The calendar component will do `new Date(datePickerValue)` under the hood.
            // If you pass a date without a time, it will treat it as UTC. By adding a time,
            // it will behave as local time. The date that comes from the server will be what
            // we expect. The time is handled separately by the nested time fieldtype.
            // https://github.com/statamic/cms/pull/6688
            return this.localValue.date + 'T00:00:00';
        },

        commonDatePickerBindings() {
            return {
                attributes: [
                    {
                        key: 'today',
                        dot: true,
                        popover: {
                            label: __('Today'),
                        },
                        dates: new Date(),
                    },
                ],
                columns: this.screens({ default: 1, lg: this.config.columns }).value,
                rows: this.screens({ default: 1, lg: this.config.rows }).value,
                expanded: this.name === 'date' || this.config.full_width,
                isRequired: this.config.required,
                locale: this.$config.get('locale').replace('_', '-'),
                masks: { input: [this.displayFormat], modelValue: this.format },
                minDate: this.config.earliest_date.date,
                maxDate: this.config.latest_date.date,
                updateOnInput: false,
                modelValue: this.datePickerValue,
                modelModifiers: { string: true, range: this.isRange },
                popover: { visibility: 'click' },
            };
        },

        format() {
            return 'YYYY-MM-DD';
        },

        displayFormat() {
            return this.meta.displayFormat;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            if (this.isRange) {
                if (!this.localValue?.start) return;

                let start = new Date(this.localValue.start.date + 'T00:00:00Z');
                let end = new Date(this.localValue.end.date + 'T00:00:00Z');

                return (
                    start.toLocaleDateString(navigator.language, { year: 'numeric', month: 'numeric', day: 'numeric' }) +
                    ' – ' +
                    end.toLocaleDateString(navigator.language, { year: 'numeric', month: 'numeric', day: 'numeric' })
                );
            }

            if (!this.localValue?.date) return;

            let date = new Date(this.localValue.date + 'T' + (this.localValue.time || '00:00:00') + 'Z');
            let preview = date.toLocaleDateString(navigator.language, { year: 'numeric', month: 'numeric', day: 'numeric' });

            if (this.hasTime && this.localValue.time) {
                preview += ' ' + date.toLocaleTimeString(navigator.language, { hour: 'numeric', minute: 'numeric'});
            }

            return preview;
        },
    },

    created() {
        this.$events.$on(`container.${this.storeName}.saving`, this.triggerChangeOnFocusedField);
    },

    mounted() {
        if (this.isRange && this.config.required && !this.value) {
            this.addDate();
        }
    },

    unmounted() {
        this.$events.$off(`container.${this.storeName}.saving`, this.triggerChangeOnFocusedField);
    },

    watch: {
        value: {
            immediate: true,
            handler(value, oldValue) {
                if (this.isRange) {
                    if (!value || !value.start) {
                        this.localValue = { start: { date: null, time: null }, end: { date: null, time: null } };
                        return;
                    }

                    let localValue = {
                        start: this.createLocalFromUtc(value.start),
                        end: this.createLocalFromUtc(value.end),
                    };

                    if (JSON.stringify(toRaw(this.localValue)) === JSON.stringify(localValue)) {
                        return;
                    }

                    this.localValue = localValue;

                    return;
                }

                if (!value || !value.date) {
                    this.localValue = { date: null, time: null };
                    return;
                }

                let localValue = this.createLocalFromUtc(value);

                if (JSON.stringify(toRaw(this.localValue)) === JSON.stringify(localValue)) {
                    return;
                }

                this.localValue = localValue;
            },
        },

        localValue(value) {
            if (this.isRange) {
                this.update({
                    start: this.createUtcFromLocal(value.start),
                    end: this.createUtcFromLocal(value.end),
                });

                return;
            }

            this.update(this.createUtcFromLocal(value));
        },
    },

    methods: {
        createLocalFromUtc(utcValue) {
            const dateTime = new Date(utcValue.date + 'T' + (utcValue.time || '00:00:00') + 'Z');

            let date =
                dateTime.getFullYear() +
                '-' +
                (dateTime.getMonth() + 1).toString().padStart(2, '0') +
                '-' +
                dateTime.getDate().toString().padStart(2, '0');
            let time =
                dateTime.getHours().toString().padStart(2, '0') +
                ':' +
                dateTime.getMinutes().toString().padStart(2, '0');

            if (this.hasSeconds) {
                time += ':' + dateTime.getSeconds().toString().padStart(2, '0');
            }

            return { date, time };
        },

        createUtcFromLocal(localValue) {
            const dateTime = new Date(localValue.date + 'T' + (localValue.time || '00:00:00'));

            let date =
                dateTime.getUTCFullYear() +
                '-' +
                (dateTime.getUTCMonth() + 1).toString().padStart(2, '0') +
                '-' +
                dateTime.getUTCDate().toString().padStart(2, '0');
            let time =
                dateTime.getUTCHours().toString().padStart(2, '0') +
                ':' +
                dateTime.getUTCMinutes().toString().padStart(2, '0');

            if (this.hasSeconds) {
                time += ':' + dateTime.getUTCSeconds().toString().padStart(2, '0');
            }

            return { date, time };
        },

        triggerChangeOnFocusedField() {
            if (!this.focusedField) return;

            this.focusedField.dispatchEvent(new Event('change'));
        },

        setLocalDate(date) {
            if (this.isRange) {
                this.localValue = {
                    start: { date: date.start, time: '00:00' },
                    end: { date: date.end, time: '23:59' },
                };

                return;
            }

            if (!date) {
                this.localValue = { date: null, time: null };
                return;
            }

            this.localValue = {
                date,
                time: this.config.time_enabled ? this.localValue.time : '00:00',
            };
        },

        setLocalTime(time) {
            this.localValue = { ...this.localValue, time };
        },

        addDate() {
            let now = new Date();

            if (!this.config.time_enabled) {
                now.setHours(0, 0, 0, 0);
            }

            let date =
                now.getFullYear() +
                '-' +
                String(now.getMonth() + 1).padStart(2, '0') +
                '-' +
                String(now.getDate()).padStart(2, '0');

            let time = now.toLocaleTimeString(undefined, {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: this.hasSeconds ? '2-digit' : undefined,
            });

            this.localValue = this.isRange
                ? { start: { date, time: '00:00' }, end: { date, time: '23:59' } }
                : { date, time };
        },
    },
};
</script>
