<template>
    <button
        type="button"
        class="toggle-container"
        :class="{ on: modelValue, 'read-only cursor-not-allowed': readOnly }"
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
    emits: ['update:model-value'],

    props: {
        modelValue: {
            type: Boolean,
        },
        readOnly: {
            type: Boolean,
            default: () => false,
        },
    },

    computed: {
        stateLiteral() {
            if (this.modelValue) {
                return 'true';
            }

            return 'false';
        },
    },

    methods: {
        toggle() {
            if (!this.readOnly) {
                this.$emit('update:model-value', !this.modelValue);
            }
        },
    },
};
</script>
