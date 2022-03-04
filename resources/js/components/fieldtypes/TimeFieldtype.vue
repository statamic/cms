<template>
    <div class="time-fieldtype-container">
        <div class="input-group">
            <div class="input-group-prepend flex items-center">
                <svg-icon name="time" class="w-4 h-4" />
            </div>
            <div
                class="input-text flex items-center px-sm w-auto"
                :class="{ 'read-only': isReadOnly }"
            >
                <template v-for="(part, index) in parts">
                    <span
                        v-if="index > 0"
                        class="colon"
                        :key="`${part}-colon`"
                    >:</span>
                    <input
                        type="text"
                        min="00"
                        placeholder="00"
                        tabindex="0"
                        ref="inputs"
                        :key="`${part}-input`"
                        :value="inputValue(index)"
                        :class="`input-time input-${part}`"
                        :max="maxes[index]"
                        :readonly="isReadOnly"
                        @input="updatePart(index, $event.target.value)"
                        @keydown.up.prevent="incrementPart(index, 1)"
                        @keydown.down.prevent="incrementPart(index, -1)"
                        @keydown.esc="clear"
                        @keydown.186.prevent="focusNextPart(index) /* colon */"
                        @keydown.190.prevent="focusNextPart(index) /* dot */"
                        @focus="$emit('focus')"
                        @blur="$emit('blur')"
                    />
                </template>
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
            maxes: [23, 59, 59],
        };
    },

    computed: {

        partCount() {
            return this.showSeconds || this.config.seconds_enabled ? 3 : 2;
        },

        parts() {
            return ['hour', 'minute', 'second'].slice(0, this.partCount);
        },

        time() {
            const time = Array(this.partCount).fill(0);

            if (this.value) {
                this.value.split(':').forEach((e, i) => { time[i] = parseInt(e); });
            }

            return time;
        },

    },

    methods: {

        inputValue(index) {
            if (this.value) {
                return this.pad(this.time[index]);
            }

            return '';
        },

        updatePart(index, value) {
            const time = [...this.time];
            time[index] = Math.max(0, Math.min(this.maxes[index], value));
            this.updateWithTime(time);
        },

        incrementPart(index, delta) {
            let value = this.time[index] + delta;

            const wrap = this.maxes[index] + 1
            while (value < 0) value += wrap
            while (value >= wrap) value -= wrap

            this.updatePart(index, value);
        },

        clear() {
            this.update(null);
        },

        updateWithTime(time) {
            this.update(this.timeToString(time));
            this.$forceUpdate();
        },

        timeToString(time) {
            return time.map(e => this.pad(e)).join(':');
        },

        pad(value) {
            return ('00' + value).slice(-2);
        },

        focusNextPart(index) {
            this.focusPart(index + 1 === this.partCount ? 0 : index + 1);
        },

        focusPart(index) {
            const input = this.$refs.inputs[index];
            input.focus();
            input.select();
        },

        focus() {
             this.$refs.inputs[0].focus();
        },

    }

};
</script>
