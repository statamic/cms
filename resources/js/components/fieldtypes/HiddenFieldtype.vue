<template>
    <input type="hidden" :name="name" v-model="data" />
</template>

<script>
export default {
    mixins: [Fieldtype]
};
</script>
