<template>
    <width-selector
        :name="name"
        :value="value"
        @input="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
        :initial-widths="config.options"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import WidthSelector from '../fields/WidthSelector.vue';

export default {
    mixins: [Fieldtype],

    components: {
        WidthSelector,
    },

    methods: {

    }
};
</script>
