<template>
    <text-input
        type="number"
        tabindex="0"
        :name="name"
        :focus="focus"
        :value="value"
        :is-read-only="isReadOnly"
        :id="fieldId"
        :min="config.min"
        :max="config.max"
        :step="config.step"
        @input="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>

export default {

    mixins: [Fieldtype],

    props: ['focus']

};
</script>
