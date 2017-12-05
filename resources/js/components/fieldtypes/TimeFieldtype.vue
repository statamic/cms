<template>
    <div class="time-template-wrapper">
        <input class="form-control"
            type="number" min="00" max="23" v-model="hour" v-el:hour
            @keydown.up.prevent="incrementHour(1)"
            @keydown.down.prevent="incrementHour(-1)"
            @keydown.esc="clear"
            @keydown.186.prevent="focusMinute"
            @keydown.190.prevent="focusMinute"
            tabindex="0"
        />
        <span class="colon">:</span>
        <input class="form-control"
            type="number" min="00" max="59" v-model="minute" v-el:minute
            @keydown.up.prevent="incrementMinute(1)"
            @keydown.down.prevent="incrementMinute(-1)"
            @keydown.esc="clear"
            tabindex="0"
        />
        <div>
            <span class="icon icon-remove" tabindex="0"
                  v-show="hasTime" v-if="hasTime"
                  @click="clear" @keyup.enter.space="clear">
                  &times;
            </span>
        </div>
    </div>
</template>

<script>

module.exports = {

    mixins: [Fieldtype],

    computed: {
        hour: {
            set: function(val) {
                this.ensureTime();
                var time = this.data.split(':');
                var hour = parseInt(val);

                // ensure you cant go beyond the range
                hour = (hour > 23) ? 23 : hour;
                hour = (hour < 0) ? 0 : hour;

                time[0] = this.pad(hour);
                this.data = time.join(':');
            },
            get: function() {
                return (this.hasTime) ? this.pad(this.data.split(':')[0]) : '';
            }
        },

        minute: {
            set: function(val) {
                this.ensureTime();
                var time = this.data.split(':');
                var minute = parseInt(val);

                // ensure you cant go beyond the range
                minute = (minute > 59) ? 59 : minute;
                minute = (minute < 0) ? 0 : minute;

                time[1] = this.pad(minute);
                this.data = time.join(':');
            },
            get: function() {
                return (this.hasTime) ? this.pad(this.data.split(':')[1]) : '';
            }
        },

        hasTime: function() {
            return this.data !== null;
        }
    },

    methods: {
        pad: function(val) {
            return ('00' + val).substr(-2, 2);
        },

        ensureTime: function() {
            if (! this.hasTime) {
                this.initializeTime();
            }
        },

        initializeTime: function() {
            this.data = '00:00';
        },

        clear: function() {
            this.data = null;
        },

        incrementHour: function(val) {
            this.ensureTime();

            var hour = parseInt(this.hour) + val;

            // enable wrapping
            hour = (hour === 24) ? 0 : hour;
            hour = (hour === -1) ? 23 : hour;

            this.hour = hour;
        },

        incrementMinute: function(val) {
            this.ensureTime();

            var minute = parseInt(this.minute) + val;

            // enable wrapping
            minute = (minute === 60) ? 0 : minute;
            minute = (minute === -1) ? 59 : minute;

            this.minute = minute;
        },

        focusMinute: function() {
            $(this.$els.minute).focus().select();
        },

        focus() {
            this.$els.hour.focus();
        }
    }
};
</script>
