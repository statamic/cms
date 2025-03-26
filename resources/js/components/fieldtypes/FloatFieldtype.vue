<template>
    <text-input
        type="text"
        tabindex="0"
        :name="name"
        :focus="focus"
        :model-value="value"
        :is-read-only="isReadOnly"
        :id="fieldId"
        @update:model-value="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur-sm')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],

    props: ['focus'],
};
</script>
