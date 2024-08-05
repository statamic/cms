<template>
    <input type="hidden" :name="name" :value="modelValue" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype]
};
</script>
