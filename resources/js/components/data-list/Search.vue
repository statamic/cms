<template>
    <div class="min-w-64 lg:w-1/3">
        <Input
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
import { Input } from '@statamic/ui';
export default {
    components: {
        Input,
    },

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
