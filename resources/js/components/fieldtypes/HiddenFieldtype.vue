<template>
    <input type="hidden" :name="name" :value="value" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype]
};
</script>
