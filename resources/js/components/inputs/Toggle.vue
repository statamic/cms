<template>
    <button
        type="button"
        class="toggle-container"
        :class="{ 'on': value, 'cursor-not-allowed read-only': readOnly }"
        @click="toggle"
        :aria-pressed="stateLiteral"
        :aria-label="__('Toggle Button')"
    >
        <div class="toggle-slider">
            <div class="toggle-knob" tabindex="0" @keyup.prevent.space.enter="toggle" ref="knob" />
        </div>
    </button>
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

    computed: {
        stateLiteral() {
            if (this.value) {
                return 'true';
            }

            return 'false';
        }
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
