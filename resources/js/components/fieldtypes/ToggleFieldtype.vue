<template>
    <div class="toggle-fieldtype-wrapper">
        <div class="toggle-container" :class="{ 'on': isOn }" @click="toggle">
            <div class="toggle-slider">
                <div class="toggle-knob" tabindex="0" @keyup.prevent.space.enter="toggle" v-el:knob tabindex="0"></div>
            </div>
        </div>
    </div>
</template>

<script>
module.exports = {

    mixins: [Fieldtype],

    data() {
        return {
            autoBindChangeWatcher: false
        };
    },

    computed: {
        isOn: function () {
            let match = true;

            // Allow the "on" state to be on when it's falsey.
            // Useful for example if the variable is "hidden" but the label is "visible".
            if (this.config && this.config.reverse) {
                match = false;
            }

            return this.data === match;
        }
    },
    methods: {
        toggle: function () {
            this.data = !this.data;
        },
        focus() {
            this.$els.knob.focus();
        }
    },
    ready() {
        if (this.data === null) {
            this.data = false;
        }

        this.bindChangeWatcher();
    }
};
</script>
