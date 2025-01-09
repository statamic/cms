<template>
    <div class="datetime min-w-[145px]">

        <button type="button" class="btn flex mb-2 md:mb-0 items-center rtl:pr-3 ltr:pl-3" v-if="!isReadOnly && config.inline === false && !hasDate" @click="addDate" tabindex="0">
            <svg-icon name="light/calendar" class="w-4 h-4 rtl:ml-2 ltr:mr-2"></svg-icon>
    		{{ __('Add Date') }}
    	</button>

        <div v-if="hasDate || config.inline"
            class="date-time-container flex flex-col @sm:flex-row gap-2"
        >
            <component
                :is="pickerComponent"
                v-bind="pickerProps"
                @input="setDate"
                @focus="focusedField = $event"
                @blur="focusedField = null"
            />

            <div v-if="config.time_enabled && !isRange" class="time-container time-fieldtype">
				<time-fieldtype
                    v-if="hasTime"
                    ref="time"
                    handle=""
                    :value="value.time"
                    :required="config.time_enabled"
                    :show-seconds="config.time_seconds_enabled"
                    :read-only="isReadOnly"
                    :config="{}"
                    @input="setTime"
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

export default {

    components: {
        SinglePopover,
        SingleInline,
        RangePopover,
        RangeInline,
    },

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            containerWidth: null,
            focusedField: null
        }
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
            return this.config.time_has_seconds;
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
            }
        },

        datePickerValue() {
            if (this.isRange) return this.value.date;

            // The calendar component will do `new Date(datePickerValue)` under the hood.
            // If you pass a date without a time, it will treat it as UTC. By adding a time,
            // it will behave as local time. The date that comes from the server will be what
            // we expect. The time is handled separately by the nested time fieldtype.
            // https://github.com/statamic/cms/pull/6688
            return this.value.date+'T00:00:00';
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
                        dates: new Date()
                    }
                ],
                columns: this.$screens({ default: 1, lg: this.config.columns }),
                rows: this.$screens({ default: 1, lg: this.config.rows }),
                isExpanded: this.name === 'date' || this.config.full_width,
                isRequired: this.config.required,
                locale: this.$config.get('locale').replace('_', '-'),
                masks: { input: [this.displayFormat] },
                minDate: this.config.earliest_date.date,
                maxDate: this.config.latest_date.date,
                modelConfig: { type: 'string', mask: this.format },
                updateOnInput: false,
                value: this.datePickerValue,
            };
        },

        datePickerEvents() {
            return {
                input: this.setDate
            };
        },

        format() {
            return 'YYYY-MM-DD';
        },

        displayFormat() {
            return this.meta.displayFormat;
        },

        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;
            if (! this.value.date) return;

            if (this.isRange) {
                return Vue.moment(this.value.date.start).format(this.displayFormat) + ' – ' + Vue.moment(this.value.date.end).format(this.displayFormat);
            }

            let preview = Vue.moment(this.value.date).format(this.displayFormat);

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
            this.value.time = Vue.moment().format(this.hasSeconds ? 'HH:mm:ss' : 'HH:mm');
        }

        this.$events.$on(`container.${this.storeName}.saving`, this.triggerChangeOnFocusedField);
    },

    destroyed() {
        this.$events.$off(`container.${this.storeName}.saving`, this.triggerChangeOnFocusedField);
    },


    methods: {

        triggerChangeOnFocusedField() {
            if (!this.focusedField) return;

            this.focusedField.dispatchEvent(new Event('change'));
        },

        setDate(date) {
            if (!date) {
                this.update({ date: null, time: null });
                return;
            }

            this.update({ ...this.value, date });
        },

        setTime(time) {
            this.update({ ...this.value, time });
        },

        addDate() {
            const now = Vue.moment().format(this.format);
            const date = this.isRange ? { start: now, end: now } : now;
            this.update({ date, time: null });
        },

    },
};
</script>
