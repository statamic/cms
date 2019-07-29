<template>
    <textarea-input
        :name="name"
        :isReadOnly="isReadOnly"
        :limit="config.character_limit || null"
        :placeholder="config.placeholder"
        :value="value"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
        @input="update" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
