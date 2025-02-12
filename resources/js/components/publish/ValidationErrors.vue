<template>
    <validation-errors :errors="errors" />
</template>

<script>
export default {
    inject: ['store'],

    computed: {
        errors() {
            return this.store.errors;
        },
    },
};
</script>
