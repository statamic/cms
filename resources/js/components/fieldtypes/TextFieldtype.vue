<template>
    <text-input :name="name" :value="value" @input="update" :autofocus="config.autofocus" :type="config.input" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
