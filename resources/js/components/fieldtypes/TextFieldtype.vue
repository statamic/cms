<template>
    <text-input
        ref="input"
        :value="value"
        :classes="config.classes"
        :focus="config.focus || name === 'title' || name === 'alt'"
        :autoselect="config.autoselect"
        :type="config.input_type"
        :isReadOnly="isReadOnly"
        :prepend="__(config.prepend)"
        :append="__(config.append)"
        :limit="config.character_limit"
        :placeholder="__(config.placeholder)"
        :name="name"
        :id="fieldId"
        @input="updateDebounced"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
