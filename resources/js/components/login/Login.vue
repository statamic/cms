<template>
    <slot v-bind="{ showEmailLogin, hasError, busy, setBusy }"></slot>
</template>

<script>
export default {
    props: {
        showEmailLogin: {
            default: false,
        },
        hasError: {
            default: false,
        },
    },

    data() {
        return {
            busy: false,
        };
    },

    mounted() {
        if (this.hasError) {
            this.$el.parentElement.parentElement.classList.add('animation-shake');
        }
    },

    methods: {
        setBusy(state = null) {
            this.busy = state ?? true;
        },
    },
};
</script>
