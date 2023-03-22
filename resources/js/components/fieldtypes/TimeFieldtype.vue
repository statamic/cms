<template>
    <div class="time-fieldtype-container">
        <div class="input-group" :class="{'w-[120px]': showSeconds || config.seconds_enabled, 'w-[96px]': ! showSeconds && ! config.seconds_enabled}">
            <div class="input-group-prepend flex items-center" v-tooltip="__('24 Hour Format')">
                <svg-icon name="time" class="w-4 h-4" />
            </div>
            <input
                type="text"
                ref="time"
                class="input-text"
                :readonly="isReadOnly"
                v-mask="mask"
                v-model="time"
                placeholder="23:45"
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
            time: this.value
        };
    },

    watch: {
        time(time) {
            this.updateTime(time);
        },
    },

    computed: {
        mask() {
            return this.showSeconds || this.config.seconds_enabled ? [/[0-2]/, /[0-9]/, ':', /[0-5]/, /[0-9]/, ':', /[0-5]/, /[0-9]/] : [/[0-2]/, /[0-9]/, ':', /[0-5]/, /[0-9]/];
        },
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

            if (parts.length === 2) {
                parts[1] = parts[1].padStart(2, '0');
                if (this.showSeconds || this.config.seconds_enabled) {
                    parts[2] = '00';
                }
            }

            if (parts.length === 3) {
                parts[2] = parts[2].padStart(2, '0');
            }

            time = parts.join(':');

            this.update(time);
        },

    }

};
</script>
