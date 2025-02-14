<template>
    <input
        type="text"
        ref="input"
        :placeholder="__(placeholder)"
        :value="value"
        @input="emitEvent"
        @keyup.esc="reset"
        autofocus
        class="input-text flex-1 bg-white text-sm outline-0 focus:border-blue-300 dark:bg-dark-600 dark:focus:border-dark-blue-125"
    />
</template>

<script>
import { debounce } from 'lodash-es';

export default {
    props: ['value'],

    props: {
        placeholder: {
            type: String,
            default: 'Search...',
        },
        value: {
            type: String,
            default: '',
        },
    },

    methods: {
        emitEvent: debounce(function (event) {
            this.$emit('input', event.target.value);
        }, 300),

        reset() {
            this.$emit('input', '');
        },

        focus() {
            this.$refs.input.focus();
        },
    },
};
</script>
