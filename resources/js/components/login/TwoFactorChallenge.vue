<template>
    <slot v-bind="{ busy, mode, toggleMode, hasError }"></slot>
</template>

<script>
export default {
    props: {
        initialMode: {
            type: String,
            default: 'code',
        },
        hasError: {
            default: false,
        },
    },

    data() {
        return {
            busy: false,
            mode: this.initialMode,
        };
    },

    mounted() {
        if (this.hasError) {
            this.$el.parentElement.parentElement.classList.add('animation-shake');
        }
    },

    methods: {
        toggleMode() {
            this.mode = this.mode === 'code' ? 'recovery_code' : 'code';
        },
    },
};
</script>
