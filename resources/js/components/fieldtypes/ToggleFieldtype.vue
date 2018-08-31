<template>
    <div class="toggle-fieldtype-wrapper">
        <div class="toggle-container" :class="{ 'on': isOn }" @click="toggle">
            <div class="toggle-slider">
                <div class="toggle-knob" tabindex="0" @keyup.prevent.space.enter="toggle" ref="knob"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {

    mixins: [Fieldtype],

    data() {
        return {
            value: this.initialValue || false
        }
    },

    computed: {
        isOn: function () {
            let match = true;

            // Allow the "on" state to be on when it's falsey.
            // Useful for example if the variable is "hidden" but the label is "visible".
            if (this.config && this.config.reverse) {
                match = false;
            }

            return this.value === match;
        }
    },
    methods: {
        toggle() {
            this.value = !this.value;
            this.update(this.value);
        },
        focus() {
            this.$refs.knob.focus();
        }
    }
};
</script>
