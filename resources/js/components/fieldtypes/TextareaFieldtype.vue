<template>
    <textarea class="form-control" v-model="data" ref="textarea" v-elastic :placeholder="config.placeholder"></textarea>
</template>

<script>
export default {
    mixins: [Fieldtype]
};
</script>
