<template>
    <text-input
        ref="input"
        :value="value"
        :classes="config.classes"
        :focus="config.focus || name === 'title'"
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
        @click="updateCursorPosition"
        @keyup.up.down="updateCursorPosition"
        @keyup.left.right="updateCursorPosition"
        @focus="$emit('focus')"
        @blur="this.blurEvent"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
