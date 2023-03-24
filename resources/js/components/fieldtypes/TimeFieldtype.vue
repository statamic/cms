<template>
    <div class="time-fieldtype-container">
        <div class="input-group" :class="{'w-[120px]': useSeconds, 'w-[96px]': ! useSeconds}">
            <button class="input-group-prepend flex items-center" v-tooltip="__('Set to now')" @click="setToNow">
                <svg-icon name="time" class="w-4 h-4" />
            </button>
            <input
                type="text"
                ref="time"
                class="input-text"
                :readonly="isReadOnly"
                v-mask="timeMask"
                v-model="time"
                :placeholder="useSeconds ? '__ : __ : __' : '__ : __'"
                @keydown.esc="clear"
                @focus="focused"
                @blur="$emit('blur')"
            />
        </div>
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    props: {
        required: {
            type: Boolean,
            default: false,
        },
        showSeconds: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            time: this.value,
        };
    },

    watch: {
        time(time) {
            if (time !== this.value) {
                this.updateTime(time);
            }
        },
        value: {
            immediate: true,
            handler(value) {
                this.time = value;
            }
        },
    },

    computed: {
        useSeconds() {
            return this.showSeconds || this.config.seconds_enabled;
        },

        timeMask() {
            return (value) => {
                const hours = [
                    /[0-9]/,
                    value.charAt(0) === '2' ? /[0-3]/ : /[0-9]/,
                ];
                const minutes = [/[0-5]/, /[0-9]/];
                const masks = [...hours, ':', ...minutes];
                const seconds = [/[0-5]/, /[0-9]/];

                if (this.useSeconds) {
                    masks.push(':', ...seconds);
                }

                if ((this.useSeconds) && value.length > 5) {
                    return [...hours, ':', ...minutes, ':', ...seconds];
                } else if (value.length > 2) {
                    return [...hours, ':', ...minutes];
                } else {
                    return hours;
                }
            }
        }
    },

    methods: {
        focused() {
            this.$refs.time.select();
            this.$emit('focus');
        },

        focus() {
             this.$refs.time.focus();
        },

        updateTime(time) {
            let parts = time.split(':');

            if (parts.length === 1 && time > 2) {
                parts[0] = parts[0].padStart(2, '0');
            }

            this.updateDebounced(parts.join(':'));
        },

        setToNow() {
            let date = new Date();
            let hours = date.getHours();
            let minutes = date.getMinutes();
            let seconds = date.getSeconds();

            if (this.useSeconds) {
                this.time = `${hours}:${minutes}:${seconds}`;
            } else {
                this.time = `${hours}:${minutes}`;
            }
        },
    }

};
</script>
