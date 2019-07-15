<template>
    <div class="toggle-container" :class="{ 'on': value, 'cursor-not-allowed': readOnly }" @click="toggle" role="checkbox">
        <div class="toggle-slider">
            <div class="toggle-knob" tabindex="0" @keyup.prevent.space.enter="toggle" ref="knob" />
        </div>
    </div>
</template>

<script>

export default {
    props: {
        value: {
            type: Boolean
        },
        readOnly: {
            type: Boolean,
            default: () => false
        },
    },

    methods: {
        toggle() {
            if (! this.readOnly) {
                this.$emit("input", !this.value)
            }
        }
    }

}
</script>
