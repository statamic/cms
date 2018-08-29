<template>

    <div v-if="hasErrors" class="bg-red text-white p-2 rounded-md shadow mb-2">
        <p v-for="(error, i) in flattenedErrors" :key="i">{{ error }}</p>
    </div>

</template>

<script>
export default {

    props: {
        errors: {
            type: Object,
            required: true
        }
    },

    computed: {

        hasErrors() {
            return Object.keys(this.errors).length > 0;
        },

        flattenedErrors() {
            return _.chain(this.errors).map(_.values).flatten().value();
        }

    }

}
</script>
