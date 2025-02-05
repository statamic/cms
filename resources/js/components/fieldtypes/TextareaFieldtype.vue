<template>
    <textarea-input
        :focus="config.focus"
        :name="name"
        :isReadOnly="isReadOnly"
        :limit="config.character_limit || null"
        :placeholder="__(config.placeholder)"
        :model-value="value"
        :id="fieldId"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
        @update:model-value="updateDebounced"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],
};
</script>
