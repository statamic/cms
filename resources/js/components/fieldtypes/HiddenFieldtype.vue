<template>
    <input type="hidden" :name="name" v-model="data" />
</template>

<script>
module.exports = {
    mixins: [Fieldtype]
};
</script>
