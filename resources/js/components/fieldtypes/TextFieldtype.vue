<template>
    <text-input
        ref="input"
        :value="value"
        :classes="config.classes"
        :autofocus="config.autofocus"
        :autoselect="config.autoselect"
        :html_type="config.html_type"
        :isReadOnly="isReadOnly"
        :prepend="config.prepend"
        :append="config.append"
        :limit="config.character_limit"
        :placeholder="config.placeholder"
        :name="name"
        @input="update"
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
