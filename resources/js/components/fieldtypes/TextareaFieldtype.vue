<template>
    <textarea-input
        :focus="config.focus"
        :name="name"
        :isReadOnly="isReadOnly"
        :limit="config.character_limit || null"
        :placeholder="__(config.placeholder)"
        :id="fieldId"
        :model-value="value"
        @update:model-value="updateDebounced"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
