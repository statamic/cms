<template>
    <div v-html="config.html" />
</template>

<script>
import Fieldtype from './Fieldtype.vue';

export default {
    mixins: [Fieldtype],
};
</script>
