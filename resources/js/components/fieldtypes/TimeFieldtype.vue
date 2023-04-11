<template>
    <div class="time-fieldtype-container">
        <div class="input-group" :class="{'w-[120px]': useSeconds, 'w-[96px]': ! useSeconds}">
            <button class="input-group-prepend flex items-center" v-tooltip="__('Set to now')" @click="setToNow">
                <svg-icon name="light/time" class="w-4 h-4" />
            </button>
            <input
                type="text"
                ref="time"
                class="input-text"
                :readonly="isReadOnly"
                v-mask="timeMask"
                v-model="inputValue"
                :placeholder="useSeconds ? '__ : __ : __' : '__ : __'"
                @keydown.esc="clear"
                @focus="focused"
                @blur="$emit('blur')"
                @change="updateActualValue"
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

    inject: ['storeName'],

    data() {
        return {
            inputValue: this.value,
        };
    },

    watch: {
        // When a user types in the text input field, this value will be updated.
        // This is not the actual value of the fieldtype, which only gets updated on the change event.
        inputValue(value, oldValue) {
            this.updateInputValue(value);
        },
        // When the value is changed via the prop (e.g. through collaboration or other JS manually
        // setting the value) we'll want to make sure it's reflected correctly here.
        value(value) {
            this.updateInputValue(value);
            this.updateActualValue();
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

    created() {
        this.$events.$on(`container.${this.storeName}.saving`, this.updateActualValue);
    },

    destroyed() {
        this.$events.$off(`container.${this.storeName}.saving`, this.updateActualValue);
    },

    methods: {
        focused() {
            this.$refs.time.select();
            this.$emit('focus');
        },

        focus() {
             this.$refs.time.focus();
        },

        // If you type 3-9 as the first digit, it will prepend a 0.
        updateInputValue(time) {
            if (! time) {
                this.inputValue = '';
                return;
            }

            let parts = time.split(':');

            if (parts.length === 1 && time > 2) {
                parts[0] = parts[0].padStart(2, '0');
            }

            this.inputValue = parts.join(':');
        },

        // This will take the value of the input, add appropriate padding, and update the actual fieldtype value.
        // e.g. 03:2    -> 03:02:00
        //      03:20   -> 03:20:00
        //      03:20:4 -> 03:20:04
        updateActualValue() {
            if (! this.inputValue) {
                this.update(null);
                return;
            }

            let parts = this.inputValue.split(':');

            if (parts.length === 1) {
                parts[0] = parts[0].padStart(2, '0');
                parts[1] = '00';
                if (this.useSeconds) {
                    parts[2] = '00';
                }
            }

            if (parts.length === 2) {
                parts[1] = parts[1].padStart(2, '0');
                if (parts[1].length > 2) {
                    parts[1] = parts[1].substr(0, 2);
                }
                if (this.useSeconds) {
                    parts[2] = '00';
                }
            }

            if (parts.length === 3) {
                parts[2] = parts[2].padStart(2, '0');
                if (parts[2].length > 2) {
                    parts[2] = parts[2].substr(0, 2);
                }
            }

            this.update(parts.join(':'));
        },

        setToNow() {
            const date = new Date();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');

            this.update(this.useSeconds
                ? `${hours}:${minutes}:${seconds}`
                : `${hours}:${minutes}`);
        },
    }

};
</script>
