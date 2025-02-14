<template>
    <div v-if="hasErrors" class="mb-4 rounded-md bg-red-500 p-4 text-white shadow">
        <p v-for="(error, i) in flattenedErrors" :key="i">{{ error }}</p>
    </div>
</template>

<script>
import { map, flatten } from 'lodash-es';

export default {
    props: {
        errors: {
            type: Object,
            required: true,
        },
    },

    computed: {
        hasErrors() {
            return Object.keys(this.errors).length > 0;
        },

        flattenedErrors() {
            return flatten(map(this.errors, (error) => error));
        },
    },
};
</script>
