<template>
    <div class="datetime">

    	<button type="button" class="btn flex items-center pl-1.5" v-if="!hasDate" @click="addDate" tabindex="0">
            <svg-icon name="calendar" class="w-4 h-4 mr-1"></svg-icon>
    		{{ __('Add Date') }}
    	</button>

    	<div class="date-time-container flex" v-if="hasDate">

    		<div class="flex-1">
                <div class="input-text cursor-text flex items-center">
                    <div class="daterange daterange--single" ref="date"></div>
                    <button class="text-blue text-xs ml-1" tabindex="0"
                        v-if="data && ! config.required"
                        @click="removeDate" @keyup.enter.space="clear">
                        {{ __('clear') }}
                    </button>
                    <svg-icon name="calendar" class="h-4 w-4 ml-1 text-grey"></svg-icon>
                </div>
    		</div>

			<div v-if="config.time_enabled" class="ml-1 time-fieldtype">
				<time-fieldtype ref="time" v-if="time" :value="time" @updated="updateTime" :required="config.time_required" :config="{}" name=""></time-fieldtype>
				<button type="button" class="btn flex items-center pl-1.5" v-if="! time" @click="addTime" tabindex="0">
					<svg-icon name="time" class="w-4 h-4 mr-1"></svg-icon>
                    <span v-text="__('Add Time')"></span>
				</button>
			</div>

    	</div>

    </div>

</template>

<script>
import Calendar from 'baremetrics-calendar';

export default {

    mixins: [Fieldtype],

    data() {
        return {
            calendar: null,
            data: null,
            time: null
        }
    },

    computed: {
        hasDate() {
            return (this.config.required) ? true : this.data !== null;
        },

        hasTime() {
            return this.data && this.data.length > 10;
        }
    },

    watch: {

        data(value) {
            this.update(value);
        }

    },

    methods: {

        /**
         * Return the date string.
         * `this.data` is the full datetime string. This will get just the date.
         */
        dateString() {
            if (this.data && this.data.length >= 10) {
                return this.data.substr(0, 10)
            } else {
                return moment().format('YYYY-MM-DD')
            }
        },

        /**
         * Updates the date string
         */
        updateDateString(dateString) {
            var timeString = (this.hasTime) ? ' ' + this.time : '';

            this.data = dateString + timeString;
        },

        /**
         * Create a watcher for the `this.time` variable.
         * Whenever the time value is updated we want to tack it onto the end
         * of the date string. Or just remove the time if it's null.
         */
        watchTime() {
            var self = this;

            this.$watch('time', function(newTime, oldTime) {
                if (newTime === null) {
                    self.data = self.dateString();
                } else {
                    self.data = self.dateString() + ' ' + newTime;
                }
            });
        },

        addTime() {
            this.time = moment().format('HH:mm');

            this.$nextTick(function() {
                $(this.$refs.time.$refs.hour).focus().select();
            });
        },

        removeTime() {
            this.time = null;
        },

        addDate() {
            this.data = moment().format('YYYY-MM-DD');
            this.$nextTick(function() {
                this.bindCalendar();
            });
        },

        removeDate() {
            this.data = null;
        },

        bindCalendar() {
            var self = this;

            // Use the date if there is one, otherwise use today's date.
            var date = (this.data)
                ? moment(self.dateString())
                : moment().format('YYYY-MM-DD');

            this.calendar = new Calendar({
                element: $(self.$refs.date),
                current_date: moment(date),
                earliest_date: self.config.earliest_date || "1900-01-01",
                format: {
                    input: self.config.format,
                    jump_month: 'MMMM',
                    jump_year: 'YYYY'
                },
                callback() {
                    var newDate = moment(this.current_date).format('YYYY-MM-DD');
                    self.updateDateString(newDate);
                }
            });
        },

        updateTime(time) {
            this.time = time;
        }

    },

    mounted() {

        const timeFormat = 'HH:mm';
        const dateFormat = 'YYYY-MM-DD';

        if (!this.data && this.required) {
            const format = (this.time_required || this.config.time_enabled)
                ? dateFormat + ' ' + timeFormat
                : dateFormat;

            this.data = moment().format(format);
        }

        else if (this.data && this.config.time_required && !this.hasTime) {
            this.data += ' ' + moment().format(timeFormat);
        }

        if (this.data) {
            this.time = this.data.substr(11);
        }

        this.watchTime();
        this.bindCalendar();
    }
};
</script>
