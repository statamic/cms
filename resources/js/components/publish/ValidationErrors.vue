<template>

    <validation-errors :errors="errors" />

</template>

<script>
export default {

    inject: ['storeName'],

    computed: {

        errors() {
            return this.$store.state.publish[this.storeName].errors;
        }

    }

}
</script>
