<template>
    <input
        type="text"
        ref="input"
        :placeholder="__('Search...')"
        :value="value"
        @input="emitEvent"
        @keyup.esc="reset"
        class="input-text flex-1 bg-white text-sm">
</template>

<script>
export default {

    props: ['value'],

    methods: {
        emitEvent: _.debounce(function (event) {
            this.$emit('input', event.target.value);
        }, 300),

        reset() {
            this.$emit('input', '');
        },

        focus() {
            this.$refs.input.focus();
        }
    },
}
</script>
