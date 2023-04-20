<template>
    <element-container @resized="containerWidth = $event.width">
    <div class="datetime">

        <button type="button" class="btn flex mb-1 md:mb-0 items-center pl-1.5" v-if="!isReadOnly && config.inline === false && !hasDate" @click="addDate" tabindex="0">
            <svg-icon name="calendar" class="w-4 h-4 mr-1"></svg-icon>
    		{{ __('Add Date') }}
    	</button>

        <div v-if="hasDate || config.inline"
            class="date-time-container"
            :class="{ 'narrow': isNarrow }"
        >

            <div class="flex-1 date-container">
                <v-date-picker
                    :attributes="attrs"
                    :class="{ 'w-full': !config.inline }"
                    :columns="$screens({ default: 1, lg: config.columns })"
                    :input-debounce="1000"
                    :is-expanded="name === 'date' || config.full_width"
                    :is-range="isRange"
                    :is-required="config.required"
                    :locale="$config.get('locale').replace('_', '-')"
                    :masks="{ input: [displayFormat] }"
                    :min-date="config.earliest_date"
                    :max-date="config.latest_date"
                    :model-config="modelConfig"
                    :popover="{ visibility: 'focus' }"
                    :rows="$screens({ default: 1, lg: config.rows })"
                    :update-on-input="true"
                    :value="datePickerValue"
                    @input="setDate"
                >
                    <template v-if="!config.inline" v-slot="{ inputValue, inputEvents }">
                        <!-- Date range inputs -->
                        <div
                            v-if="isRange"
                            class="w-full flex items-center"
                            :class="{ 'flex-col': isNarrow }"
                        >
                            <div class="input-group">
                                <div class="input-group-prepend flex items-center" v-if="!config.inline">
                                    <svg-icon name="calendar" class="w-4 h-4" />
                                </div>
                                <div class="input-text border border-grey-50 border-l-0" :class="{ 'read-only': isReadOnly }">
                                    <input
                                        class="input-text-minimal p-0 bg-transparent leading-none"
                                        :value="inputValue.start"
                                        :readonly="isReadOnly"
                                        v-on="!isReadOnly && inputEvents.start"
                                    />
                                </div>
                            </div>

                            <div class="icon icon-arrow-right my-sm mx-1 text-grey-60" />

                            <div class="input-group">
                                <div class="input-group-prepend flex items-center" v-if="!config.inline">
                                    <svg-icon name="calendar" class="w-4 h-4" />
                                </div>
                                <div class="input-text border border-grey-50 border-l-0" :class="{ 'read-only': isReadOnly }">
                                    <input
                                        class="input-text-minimal p-0 bg-transparent leading-none"
                                        :value="inputValue.end"
                                        :readonly="isReadOnly"
                                        v-on="!isReadOnly && inputEvents.end"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Single date input -->
                        <div v-else class="input-group">
                            <div class="input-group-prepend flex items-center" v-if="!config.inline">
                                <svg-icon name="calendar" class="w-4 h-4" />
                            </div>
                            <div class="input-text border border-grey-50 border-l-0" :class="{ 'read-only': isReadOnly }">
                                <input
                                    class="input-text-minimal p-0 bg-transparent leading-none"
                                    :value="inputValue"
                                    :readonly="isReadOnly"
                                    v-on="!isReadOnly && inputEvents"
                                />
                            </div>
                        </div>
                    </template>
                </v-date-picker>
            </div>

            <div v-if="config.time_enabled && !isRange" class="time-container time-fieldtype">
				<time-fieldtype
                    v-if="hasTime"
                    ref="time"
                    handle=""
                    :value="timeString"
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
            containerWidth: null
        }
    },

    computed: {

        hasDate() {
            return this.config.required || this.value;
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
            return this.isRange ? this.value : this.value.replace(' ', 'T');
        },

        timeString() {
            return Vue.moment(this.value).format('HH:mm:ss');
        },

        format() {
            return (this.hasTime) ? 'YYYY-MM-DD HH:mm:ss' : 'YYYY-MM-DD';
        },

        displayFormat() {
            return this.meta.displayFormat;
        },

        isNarrow() {
            return this.containerWidth <= 320;
        },

    },


    methods: {

        setDate(date) {
            if (!date) {
                this.update(null);
                return;
            }

            if (this.isRange) {
                this.setDateRange(date);
            } else {
                this.setSingleDate(date);
            }
        },

        setSingleDate(date) {
            const moment = Vue.moment(date);

            if (this.hasTime) {
                const timeMoment = Vue.moment(this.value);
                moment
                    .hour(timeMoment.hour())
                    .minute(timeMoment.minute())
                    .second(this.hasSeconds ? timeMoment.second() : 0);
            }

            if (moment.isValid()) {
                this.update(moment.format(this.format));
            }
        },

        setDateRange(range) {
            if (Vue.moment(range.start).isValid() && Vue.moment(range.end).isValid()) {
                this.update(range);
            }
        },

        setTime(timeString) {
            const [hour, minute, second] = timeString.split(':');

            const moment = Vue.moment(this.value) // clone before mutating
                .hour(hour)
                .minute(minute)
                .second(second)

            if (moment.isValid()) {
                this.update(moment.format(this.format));
            }
        },

        addDate() {
            const now = Vue.moment().format(this.format);
            this.update(this.isRange ? { start: now, end: now } : now);
        },

    },
};
</script>
