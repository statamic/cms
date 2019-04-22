<template>
    <textarea-input :name="name" :value="value" @input="update" :limit="config.character_limit || null" :isReadOnly="isReadOnly" @focus="$emit('focus')" @blur="$emit('blur')" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
