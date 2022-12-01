<template>
    <text-input
        ref="input"
        :value="value"
        :classes="config.classes"
        :focus="config.focus || name === 'title' || name === 'alt'"
        :autoselect="config.autoselect"
        :type="config.input_type"
        :isReadOnly="isReadOnly"
        :prepend="config.prepend"
        :append="config.append"
        :limit="config.character_limit"
        :placeholder="config.placeholder"
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
