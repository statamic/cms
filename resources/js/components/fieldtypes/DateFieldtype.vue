<template>
    <div class="datetime min-w-[145px]">

        <button type="button" class="btn flex mb-2 md:mb-0 items-center pl-3" v-if="!isReadOnly && config.inline === false && !hasDate" @click="addDate" tabindex="0">
            <svg-icon name="light/calendar" class="w-4 h-4 mr-2"></svg-icon>
    		{{ __('Add Date') }}
    	</button>

        <div v-if="hasDate || config.inline"
            class="date-time-container flex flow-col @sm:flex-row"
            :class="config.time_seconds_enabled ? 'space-x-1' : 'space-x-3'"
        >

            <div class="flex-1 date-container">
                <v-date-picker
                    :attributes="attrs"
                    :class="{ 'w-full': !config.inline }"
                    :columns="$screens({ default: 1, lg: config.columns })"
                    :is-expanded="name === 'date' || config.full_width"
                    :is-range="isRange"
                    :is-required="config.required"
                    :locale="$config.get('locale').replace('_', '-')"
                    :masks="{ input: [displayFormat] }"
                    :min-date="config.earliest_date.date"
                    :max-date="config.latest_date.date"
                    :model-config="modelConfig"
                    :popover="{ visibility: 'focus' }"
                    :rows="$screens({ default: 1, lg: config.rows })"
                    :update-on-input="false"
                    :value="datePickerValue"
                    @input="setDate"
                >
                    <template v-if="!config.inline" v-slot="{ inputValue, inputEvents }">
                        <!-- Date range inputs -->
                        <div
                            v-if="isRange"
                            class="w-full flex items-start @md:items-center flex-col @md:flex-row"
                        >
                            <div class="input-group">
                                <div class="input-group-prepend flex items-center" v-if="!config.inline">
                                    <svg-icon name="light/calendar" class="w-4 h-4" />
                                </div>
                                <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                                    <input
                                        class="input-text-minimal p-0 bg-transparent leading-none"
                                        :value="inputValue.start"
                                        :readonly="isReadOnly"
                                        @focus="focusedField = $event.target"
                                        @blur="focusedField = null"
                                        v-on="!isReadOnly && inputEvents.start"
                                    />
                                </div>
                            </div>

                            <svg-icon name="micro/arrow-right" class="w-6 h-6 my-1 mx-2 text-gray-700 hidden @md:block" />
                            <svg-icon name="micro/arrow-right" class="w-3.5 h-3.5 my-2 mx-2.5 rotate-90 text-gray-700 @md:hidden" />

                            <div class="input-group">
                                <div class="input-group-prepend flex items-center" v-if="!config.inline">
                                    <svg-icon name="light/calendar" class="w-4 h-4" />
                                </div>
                                <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                                    <input
                                        class="input-text-minimal p-0 bg-transparent leading-none"
                                        :value="inputValue.end"
                                        :readonly="isReadOnly"
                                        @focus="focusedField = $event.target"
                                        @blur="focusedField = null"
                                        v-on="!isReadOnly && inputEvents.end"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Single date input -->
                        <div v-else class="input-group">
                            <div class="input-group-prepend flex items-center" v-if="!config.inline">
                                <svg-icon name="light/calendar" class="w-4 h-4" />
                            </div>
                            <div class="input-text border border-gray-500 border-l-0" :class="{ 'read-only': isReadOnly }">
                                <input
                                    ref="singleDateInput"
                                    class="input-text-minimal p-0 bg-transparent leading-none"
                                    :value="inputValue"
                                    :readonly="isReadOnly"
                                    @focus="focusedField = $event.target"
                                    @blur="focusedField = null"
                                    v-on="!isReadOnly && inputEvents"
                                />
                            </div>
                        </div>
                    </template>
                </v-date-picker>
            </div>

            <div v-if="config.time_enabled && !isRange" class="time-container @xs:ml-2 @xs:mt-0 time-fieldtype">
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
    </element-container>

</template>

<script>

export default {

    mixins: [Fieldtype],

    inject: ['storeName'],

    data() {
        return {
            attrs: [
                {
                    key: 'today',
                    dot: true,
                    popover: {
                        label: __('Today'),
                    },
                    dates: new Date()
                }
            ],
            containerWidth: null,
            focusedField: null
        }
    },

    computed: {

        hasDate() {
            return this.config.required || this.value.date;
        },

        hasTime() {
            return this.config.time_enabled && !this.isRange;
        },

        hasSeconds() {
            return this.config.time_has_seconds;
        },

        isRange() {
            return this.config.mode === 'range';
        },

        modelConfig() {
            return {
                type: 'string',
                mask: this.format,
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

        format() {
            return 'YYYY-MM-DD';
        },

        displayFormat() {
            return this.meta.displayFormat;
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
