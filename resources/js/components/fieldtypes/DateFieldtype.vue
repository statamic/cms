<template>
    <element-container @resized="containerWidth = $event.width">
    <div class="datetime">

        <button type="button" class="btn flex mb-1 md:mb-0 items-center pl-1.5" v-if="!isReadOnly && config.inline === false && !hasDate" @click="addDate" tabindex="0">
            <svg-icon name="calendar" class="w-4 h-4 mr-1"></svg-icon>
    		{{ __('Add Date') }}
    	</button>

        <div v-if="hasDate || config.inline"
            class="date-time-container"
            :class="{ 'narrow': containerWidth <= 260 }"
        >

            <div class="flex-1 date-container" :class="{'input-group': !config.inline }">
                <div class="input-group-prepend flex items-center" v-if="!config.inline">
                    <svg-icon name="calendar" class="w-4 h-4" />
                </div>
                <input type="text" class="input-text" readonly :value="$moment(value).format('L')" v-if="isReadOnly">
                <v-date-picker
                    v-else
                    v-model="date"
                    :popover="{ visibility: 'click' }"
                    :class="{'input-text border border-grey-50 border-l-0': !config.inline }"
                    :attributes="attrs"
                    :locale="$config.get('locale')"
                    :formats="formats"
                    :mode="config.mode"
                    :input="value"
                    :is-required="config.required"
                    :is-inline="config.inline"
                    :is-expanded="name === 'date' || config.full_width"
                    :columns="$screens({ default: 1, lg: config.columns })"
                    :rows="$screens({ default: 1, lg: config.rows })">
                        <input
                            slot-scope="{ inputProps, inputEvents }"
                            class="bg-transparent leading-none w-full focus:outline-none"
                            v-bind="inputProps"
                            v-on="inputEvents" />
                </v-date-picker>
            </div>

            <div v-if="config.time_enabled && config.mode === 'single'" class="time-container time-fieldtype">
				<time-fieldtype ref="time" v-if="time" v-model="time" :required="config.time_enabled && config.time_required" :read-only="isReadOnly" :config="{}" handle=""></time-fieldtype>
				<button type="button" class="btn flex items-center pl-1.5" v-if="! time" @click="addTime" tabindex="0">
					<svg-icon name="time" class="w-4 h-4 mr-1"></svg-icon>
                    <span v-text="__('Add Time')"></span>
				</button>
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
            date: null,
            time: null,
            formats: {
                title: 'MMMM YYYY',
                weekdays: 'W',
                navMonths: 'MMM',
                input: ['L', 'YYYY-MM-DD HH:mm', 'YYYY-MM-DD'],
                dayPopover: 'L',
            },
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
            return (this.config.required) ? true : this.date !== null;
        },

        dateTime() {
            return Vue.moment(this.date).set({'hour': this.hour, 'minute': this.minutes}).format(this.format);
        },

        hour() {
            return this.time ? this.time.split(':')[0] : 0;
        },

        minutes() {
            return this.time ? this.time.split(':')[1] : 0;
        },

        format() {
            return (this.time) ? 'YYYY-MM-DD HH:mm' : 'YYYY-MM-DD';
        }
    },

    watch: {

        value: {
            immediate: true,
            handler(value) {
                this.date = this.parseDate(value);
                this.time = this.parseTime(value);
            }
        },

        date(value, oldValue) {
            if (JSON.stringify(value) === JSON.stringify(oldValue)) return;
            this.handleUpdate(value)
        },

        time(value) {
            this.handleUpdate(value)
        }

    },

    methods: {
        handleUpdate(value) {
            let date;
            if (this.config.mode === "range") {
                date = value ? {
                    start: Vue.moment(value.start).format(this.format),
                    end: Vue.moment(value.end).format(this.format)
                } : null;
            } else {
                date = Vue.moment(this.dateTime).format(this.format);
            }

            if (date == 'Invalid date') {
                date = null;
            }

            this.update(date);
        },

        addDate() {
            this.date = Vue.moment().format(this.format);
            if (this.config.time_enabled && this.config.time_required) {
                this.addTime();
            }
        },

        addTime() {
            this.time = Vue.moment().format('HH:mm');
            this.$nextTick(function() {
                $(this.$refs.time.$refs.hour).focus().select();
            });
        },

        removeTime() {
            this.time = null;
        },

        parseDate(value) {
            if (value) {
                if (this.config.mode === "single") {
                    return Vue.moment(value).toDate()
                } else if (this.config.mode === "range") {
                    return {
                        'start': Vue.moment(value.start).toDate(),
                        'end': Vue.moment(value.end).toDate()
                    }
                }
             } else {
                 return (this.config.required) ?  Vue.moment().toDate() : null
             }
        },

        parseTime(value) {
            if (value && this.config.time_enabled) {
                return Vue.moment(value).format('HH:mm');
            } else if (this.config.time_required) {
                return Vue.moment().format('HH:mm');
            } else {
                return null;
            }
        }
    },
};
</script>
