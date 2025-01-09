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
        :prepend="__(config.prepend)"
        :append="__(config.append)"
        :placeholder="__(config.placeholder)"
        @input="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype],
    props: {
        prepend: { default: null },
        append: { default: null },
    }
};
</script>
