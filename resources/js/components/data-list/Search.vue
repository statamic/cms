<template>
    <input
        type="text"
        ref="input"
        :placeholder="__(placeholder)"
        :value="modelValue"
        @input="emitEvent"
        @keyup.esc="reset"
        autofocus
        class="input-text flex-1 bg-white text-sm outline-0 focus:border-blue-300 dark:bg-dark-600 dark:focus:border-dark-blue-125"
    />
</template>

<script>
import debounce from '@statamic/util/debounce.js';

export default {
    props: {
        placeholder: {
            type: String,
            default: 'Search...',
        },
        modelValue: {
            type: String,
            default: '',
        },
    },

    methods: {
        emitEvent: debounce(function (event) {
            this.$emit('update:model-value', event.target.value);
        }, 300),

        reset() {
            this.$emit('update:model-value', '');
        },

        focus() {
            this.$refs.input.focus();
        },
    },
};
</script>
