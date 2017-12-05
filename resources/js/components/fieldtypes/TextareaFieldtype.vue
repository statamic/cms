<template>
    <textarea class="form-control" v-model="data" v-el:textarea v-elastic></textarea>
</template>

<script>
module.exports = {
    mixins: [Fieldtype]
};
</script>
