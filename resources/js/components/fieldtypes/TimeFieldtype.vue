<template>
    <div class="time-fieldtype-container">
        <div class="input-group">
            <button class="input-group-prepend flex items-center" v-tooltip="__('Set to now')" @click="setToNow" v-if="!isReadOnly">
                <svg-icon name="light/time" class="w-4 h-4" />
            </button>
            <input
                type="time"
                ref="time"
                class="input-text [&::-webkit-calendar-picker-indicator]:hidden"
                :step="useSeconds ? '1' : null"
                :readonly="isReadOnly"
                @keydown.esc="clear"
                @focus="focused"
                @blur="$emit('blur')"
                @change="updateActualValue"
            />
        </div>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import IMask from 'imask';

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
            mask: null,
        };
    },

    watch: {
        // We use this instead of v-model or :value because the mask library wants to be in control of the value.
        inputValue(value) {
            this.mask.value = value;
        },
        // When the value is changed via the prop (e.g. through collaboration or other JS manually
        // setting the value) we'll want to make sure it's reflected correctly here.
        value(value) {
            this.inputValue = value;
            this.updateActualValue();
        },
    },

    computed: {
        useSeconds() {
            return this.showSeconds || this.config.seconds_enabled;
        }
    },

    created() {
        this.$events.$on(`container.${this.storeName}.saving`, this.updateActualValue);
    },

    mounted() {
        // The mask will replace the need for binding the value to the input.
        this.mask = IMask(this.$refs.time, {
            mask: this.useSeconds ? '0[0]:`0[0]:`00' : '0[0]:`00'
        });

        // Bind initial value to mask.
        this.mask.value = new String(this.inputValue);

        // We use this instead of v-model or @input because input would be early and give us the raw value.
        // In this event listener, we get masked value (with colons/guides). // e.g. 032 vs. 03:2
        this.mask.on('accept', e => this.inputValue = this.mask.value);
    },

    destroyed() {
        this.$events.$off(`container.${this.storeName}.saving`, this.updateActualValue);
        this.mask.destroy();
    },

    methods: {
        focused() {
            this.$refs.time.select();
            this.$emit('focus');
        },

        focus() {
             this.$refs.time.focus();
        },

        // This will take the value of the input, add appropriate padding, and update the actual fieldtype value.
        // e.g. 03:2    -> 03:02:00
        //      03:20   -> 03:20:00
        //      03:20:4 -> 03:20:04
        //      3:2:4   -> 03:02:04
        updateActualValue() {
            if (! this.inputValue) {
                this.update(null);
                return;
            }

            let parts = this.inputValue.split(':');
            if (parts.length === 1) parts.push('00');
            if (parts.length === 2 && this.useSeconds) parts.push('00');
            parts = parts.map(part => part.padStart(2, '0'));

            let newValue = parts.join(':');

            if (this.value !== newValue) this.update(newValue);
            this.inputValue = newValue;
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

        adjustPart(e, direction, callback) {
            const caretPosition = e.target.selectionStart;
            const time = this.inputValue.split(':');

            let part = 0;
            if (caretPosition > 5) {
                part = 2;
            } else if (caretPosition > 2) {
                part = 1;
            }

            const current = parseInt(time[part]);
            const newValue = current + (direction === 'increment' ? 1 : -1);

            const returned = callback(part, newValue);
            if (returned) {
                time[part] = returned;
            } else {
                time[part] = String(newValue).padStart(2, '0');
            }

            this.update(time.join(':'));

            // Set the caret position back to where it was
            this.$nextTick(() => {
                e.target.selectionStart = caretPosition;
                e.target.selectionEnd = caretPosition;
            });
        },
    }

};
</script>
