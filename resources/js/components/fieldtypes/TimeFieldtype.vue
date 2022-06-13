<template>
    <div class="time-fieldtype-container">
        <div class="input-group">
            <div class="input-group-prepend flex items-center">
                <svg-icon name="time" class="w-4 h-4" />
            </div>
            <div class="input-text flex items-center px-sm w-auto" :class="{ 'read-only': isReadOnly }">
                <input class="input-time input-hour"
                    type="text" min="00" max="23" v-model="hour" ref="hour"
                    placeholder="00"
                    @keydown.up.prevent="incrementHour(1)"
                    @keydown.down.prevent="incrementHour(-1)"
                    @keydown.esc="clear"
                    @keydown.186.prevent="focusMinute"
                    @keydown.190.prevent="focusMinute"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')"
                    :readonly="isReadOnly"
                    :id="fieldId"
                    tabindex="0"
                />
                <span class="colon">:</span>
                <input class="input-time input-minute"
                    type="text" min="00" max="59" v-model="minute" ref="minute"
                    placeholder="00"
                    @keydown.up.prevent="incrementMinute(1)"
                    @keydown.down.prevent="incrementMinute(-1)"
                    @keydown.esc="clear"
                    @focus="$emit('focus')"
                    @blur="$emit('blur')"
                    :readonly="isReadOnly"
                    tabindex="0"
                />
            </div>
        </div>
        <button class="text-xl text-grey-60 hover:text-grey-80 h-4 w-4 p-1 flex items-center outline-none" tabindex="0"
              v-if="! required && ! isReadOnly"
              @click="clear" @keyup.enter.space="clear">
              &times;
        </button>
    </div>
</template>

<script>

export default {

    mixins: [Fieldtype],

    props: {
        required: Boolean
    },

    data() {
        return {
            data: this.value
        }
    },

    watch: {

        value(value) {
            this.data = value;
        },

        data(value) {
            this.update(value);
        }

    },

    computed: {
        hour: {
            set: function(val) {
                this.ensureTime();
                var time = this.data.split(':');
                var hour = parseInt(val);

                hour = isNaN(hour) ? 0 : hour;

                // ensure you cant go beyond the range
                hour = (hour > 23) ? 23 : hour;
                hour = (hour < 0) ? 0 : hour;

                time[0] = this.pad(hour);
                this.data = time.join(':');

                // ensure the input value is updated
                this.$forceUpdate();
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

                minute = isNaN(minute) ? 0 : minute;

                // ensure you cant go beyond the range
                minute = (minute > 59) ? 59 : minute;
                minute = (minute < 0) ? 0 : minute;

                time[1] = this.pad(minute);
                this.data = time.join(':');

                // ensure the input value is updated
                this.$forceUpdate();
            },
            get: function() {
                return (this.hasTime) ? this.pad(this.data.split(':')[1]) : '';
            }
        },

        hasTime: function() {
            return this.required || this.data !== null;
        },
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
            $(this.$refs.minute).focus().select();
        },

        focus() {
            this.$refs.hour.focus();
        }
    }
};
</script>
