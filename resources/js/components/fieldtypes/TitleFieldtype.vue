<template>
    <input type="text" v-model="data" class='title-fieldtype-input' />
</template>

<script>
module.exports = {
    props: ['data', 'config', 'name']
};
</script>
