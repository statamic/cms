<template>
    <div class="min-w-64 lg:w-1/3">
        <ui-input
            autofocus
            ref="input"
            icon="magnifying-glass"
            :placeholder="__(placeholder)"
            :value="value"
            @input="emitEvent"
            @keyup.esc="reset"
        />
    </div>
</template>

<script>
import debounce from '@statamic/util/debounce.js';

export default {
    props: ['value'],

    props: {
        placeholder: {
            type: String,
            default: 'Filter...',
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
