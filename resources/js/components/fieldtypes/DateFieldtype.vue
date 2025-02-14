<template>
    <div class="datetime min-w-[145px]">
        <p>UTC: {{ value }}</p>
        <p>Local: {{ localValue }}</p>

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
import { isProxy, toRaw } from 'vue';

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
            mounted: false,
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
            // todo: ranges
            if (this.isRange) return this.value.date;

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
            if (!this.value.date) return;

            if (this.isRange) {
                return (
                    this.$moment(this.value.date.start).format(this.displayFormat) +
                    ' – ' +
                    this.$moment(this.value.date.end).format(this.displayFormat)
                );
            }

            let preview = this.$moment(this.value.date).format(this.displayFormat);

            if (this.hasTime && this.value.time) {
                preview += ` ${this.value.time}`;
            }

            return preview;
        },
    },

    created() {
        if (this.value.time === 'now') {
            // Probably shouldn't be modifying a prop, but luckily it all works nicely, without
            // needing to create an "update value without triggering dirty state" flow yet.
            this.value.time = this.$moment().format(this.hasSeconds ? 'HH:mm:ss' : 'HH:mm'); // todo: utc me
        }

        this.$events.$on(`container.${this.storeName}.saving`, this.triggerChangeOnFocusedField);
    },

    mounted() {
        this.mounted = true;
    },

    unmounted() {
        this.$events.$off(`container.${this.storeName}.saving`, this.triggerChangeOnFocusedField);
    },

    watch: {
        value: {
            immediate: true,
            handler(value, oldValue) {
                let localValue = this.createLocalFromUtc(value);

                if (JSON.stringify(toRaw(this.localValue)) === JSON.stringify(localValue)) {
                    return;
                }

                this.localValue = localValue;
            },
        },

        localValue(value) {
            if (! this.mounted) {
                return;
            }

            this.update(this.createUtcFromLocal(value));
        },
    },

    methods: {
        createLocalFromUtc(utcValue) {
            const localTime = new Date(utcValue.date + 'T' + (utcValue.time || '00:00:00') + 'Z');

            let date = localTime.getFullYear() + '-' + (localTime.getMonth() + 1).toString().padStart(2, '0') + '-' + localTime.getDate().toString().padStart(2, '0');
            let time = null;

            if (this.hasTime) {
                time = localTime.getHours().toString().padStart(2, '0') + ':' + localTime.getMinutes().toString().padStart(2, '0');

                if (this.hasSeconds) {
                    time += ':' + localTime.getSeconds().toString().padStart(2, '0');
                }
            } else {
                time = '00:00';
            }

            return { date, time };
        },

        createUtcFromLocal(localValue) {
            const utcTime = new Date(localValue.date + 'T' + (this.hasTime ? localValue.time : '00:00:00'));

            let date = utcTime.getUTCFullYear() + '-' + (utcTime.getUTCMonth() + 1).toString().padStart(2, '0') + '-' + utcTime.getUTCDate().toString().padStart(2, '0');
            let time = null;

            // if (this.hasTime) {
                time = utcTime.getUTCHours().toString().padStart(2, '0') + ':' + utcTime.getUTCMinutes().toString().padStart(2, '0');

                if (this.hasSeconds) {
                    time += ':' + utcTime.getUTCSeconds().toString().padStart(2, '0');
                }
            // }

            return { date, time };
        },

        triggerChangeOnFocusedField() {
            if (!this.focusedField) return;

            this.focusedField.dispatchEvent(new Event('change'));
        },

        setLocalDate(date) {
            if (!date) {
                this.localValue = { date: null, time: null };
                return;
            }

            this.localValue = { ...this.localValue, date };
        },

        setLocalTime(time) {
            this.localValue = { ...this.localValue, time };
        },

        addDate() {
            const now = this.$moment().format(this.format);
            const date = this.isRange ? { start: now, end: now } : now;
            this.update({ date, time: null });
        },
    },
};
</script>
