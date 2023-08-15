<template>
    <text-input
        ref="input"
        type="number"
        :name="name"
        :focus="config.focus"
        :value="value"
        :is-read-only="isReadOnly"
        :id="fieldId"
        :min="config.min"
        :prepend="config.prepend"
        :append="config.append"
        @input="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>

export default {

    mixins: [Fieldtype],
    props: {
        prepend: { default: null },
        append: { default: null },
    }
};
</script>
