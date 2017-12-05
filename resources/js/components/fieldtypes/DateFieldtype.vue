<template>
    <div class="datetime clearfix">

    	<button type="button" class="btn btn-default add-date" v-if="!hasDate" @click="addDate" tabindex="0">
    		{{ translate('cp.add_date') }}
    	</button>

    	<div v-if="hasDate" class="date-time-container">

    		<div class="col-date">
    			<div class="daterange daterange--single" :data-datetime="date" v-el:date>
    				<span class="icon icon-calendar"></span>
    				<span class="icon icon-remove" @click="removeDate" v-if="blankAllowed">&times;</span>
    			</div>
    		</div>
    		<div class="col-time">
    			<div class="time-fieldtype" v-if="timeAllowed">
    				<time-fieldtype v-ref:time v-show="hasTime" :data.sync="time"></time-fieldtype>
    				<button type="button" class="btn btn-default btn-icon add-time" v-show="!hasTime" @click="addTime" tabindex="0">
    					<span class="icon icon-clock"></span>
    				</button>
    			</div>
    		</div>

    	</div>

    </div>

</template>

<script>

module.exports = {

    mixins: [Fieldtype],

    props: {
        name: String,
        data: {},
        config: { default: function() { return {}; } },
    },

    data: function() {
        return {
            calendar: null,
            time: null,
            autoBindChangeWatcher: false
        }
    },

    computed: {
        hasDate: function() {
            if (this.blankAllowed) {
                return this.data !== null;
            } else {
                return true;
            }
        },

        hasTime: function() {
            return this.data && this.data.length > 10;
        },

        timeAllowed: function() {
            return this.config.allow_time !== false;
        },

        blankAllowed: function() {
            return this.config.allow_blank === true;
        }
    },

    methods: {

        /**
         * Return the date string.
         * `this.data` is the full datetime string. This will get just the date.
         */
        dateString: function() {
            if (this.data && this.data.length >= 10) {
                return this.data.substr(0, 10)
            } else {
                return moment().format('YYYY-MM-DD')
            }
        },

        /**
         * Updates the date string
         */
        updateDateString: function(dateString) {
            var timeString = (this.hasTime) ? ' ' + this.time : '';

            this.data = dateString + timeString;
        },

        /**
         * Create a watcher for the `this.time` variable.
         * Whenever the time value is updated we want to tack it onto the end
         * of the date string. Or just remove the time if it's null.
         */
        watchTime: function() {
            var self = this;

            this.$watch('time', function(newTime, oldTime) {
                if (newTime === null) {
                    self.data = self.dateString();
                } else {
                    self.data = self.dateString() + ' ' + newTime;
                }
            });
        },

        addTime: function() {
            this.time = moment().format('HH:mm');

            this.$nextTick(function() {
                $(this.$refs.time.$els.hour).focus().select();
            });
        },

        removeTime: function() {
            this.time = null;
        },

        addDate: function() {
            this.data = moment().format('YYYY-MM-DD');
            this.$nextTick(function() {
                this.bindCalendar();
            });
        },

        removeDate: function() {
            this.data = null;
        },

        bindCalendar: function() {
            var self = this;

            // Use the date if there is one, otherwise use today's date.
            var date = (this.data)
                ? moment(self.dateString())
                : moment().format('YYYY-MM-DD');

            this.calendar = new Calendar({
                element: $(self.$el).find('.daterange'),
                current_date: moment(date),
                earliest_date: this.config.earliest_date || "January 1, 1900",
                callback: function() {
                    var newDate = moment(this.current_date).format('YYYY-MM-DD');
                    self.updateDateString(newDate);
                }
            });
        },

        focus() {
            setTimeout(() => $(this.$els.date).find('.dr-input .dr-date').click(), 200);
        }

    },

    ready: function() {
        if (this.data) {
            this.time = this.data.substr(11);
        }

        // If there's no data (ie. a blank field) and blanks are _not_ allowed, we want
        // to initialize the data to the current date, so that the value will get
        // saved without the user needing to interact with the field first.
        if (!this.data && !this.blankAllowed) {
            this.data = moment().format('YYYY-MM-DD');
        }

        this.watchTime();
        this.bindCalendar();
        this.bindChangeWatcher();
    }
};
</script>
