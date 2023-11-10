<template>
    <div class="html-fieldtype-wrapper" v-html="config.html" />
</template>

<script>
export default {

    mixins: [Fieldtype],

    inject: ['storeName'],
  
};
</script>
