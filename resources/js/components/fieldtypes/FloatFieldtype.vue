<template>
    <text-input
        type="text"
        tabindex="0"
        :name="name"
        :focus="focus"
        :value="value"
        :is-read-only="isReadOnly"
        :id="fieldId"
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
