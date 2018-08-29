<template>
    <textarea-input :value="initialValue" @input="update" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
