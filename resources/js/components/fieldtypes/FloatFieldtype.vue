<template>
    <text-input
        type="text"
        tabindex="0"
        :name="name"
        :focus="focus"
        :is-read-only="isReadOnly"
        :id="fieldId"
        :model-value="modelValue"
        @update:model-value="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],
    props: ['focus']
};
</script>
