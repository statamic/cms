<template>
    <text-input
        :name="name"
        :value="value"
        @input="update"
        :autofocus="config.autofocus"
        :type="config.input"
        :isReadOnly="isReadOnly"
        :prepend="config.prepend"
        :append="config.append"
        :limit="config.character_limit"
        :placeholder="config.placeholder"
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
