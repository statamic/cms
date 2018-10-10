<template>
    <text-input :name="name" :value="value" @input="update" :autofocus="config.autofocus" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {

    mixins: [Fieldtype]

}
</script>
