<template>
    <text-input :name="name" :value="value" @input="update" :autofocus="config.autofocus" :type="config.input" :isReadOnly="isReadOnly" @focus="$emit('focus')" @blur="$emit('blur')" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
