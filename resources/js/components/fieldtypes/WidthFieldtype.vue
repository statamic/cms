<template>
    <width-selector
        :name="name"
        :model-value="modelValue"
        @update:model-value="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
        :initial-widths="config.options"
    />
</template>

<script>
import WidthSelector from '../fields/WidthSelector.vue';
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

    components: {
        WidthSelector,
    },
};
</script>
