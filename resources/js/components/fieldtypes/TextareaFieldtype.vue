<template>
    <textarea-input :name="name" :value="value" @input="update" :limit="config.character_limit || null" :isReadOnly="isReadOnly" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
