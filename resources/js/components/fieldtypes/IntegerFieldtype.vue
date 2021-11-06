<template>
    <text-input
        ref="input"
        type="number"
        :name="name"
        :focus="config.focus"
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

};
</script>
