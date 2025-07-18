<template>
    <div class="flex-1 max-w-md">
        <Input
            autofocus
            ref="input"
            icon="magnifying-glass"
            :placeholder="__(placeholder)"
            :value="modelValue"
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
            this.$refs.input.$el.querySelector('input').focus();
        },
    },
};
</script>
