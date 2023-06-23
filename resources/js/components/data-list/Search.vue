<template>
    <input
        type="text"
        ref="input"
        :placeholder="__(placeholder)"
        :value="value"
        @input="emitEvent"
        @keyup.esc="reset"
        autofocus
        class="input-text flex-1 bg-white text-sm focus:border-blue-300 outline-0">
</template>

<script>
export default {

    props: [
        'value'
    ],

    props: {
        placeholder: {
            type: String,
            default: 'Search...'
        },
        value: {
            type: String,
            default: ''
        }
    },

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
