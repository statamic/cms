<template>
    <textarea-input :name="name" :value="value" @input="update" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
