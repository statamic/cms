<template>

    <div class="publish-fields">

        <publish-field
            v-for="field in fields"
            :key="field.handle"
            :config="field"
            :value="values[field.handle]"
            :meta="meta[field.handle]"
            :errors="errors[field.handle]"
            @updated="updated"
        />

    </div>

</template>

<script>
import PublishField from './Field.vue';

export default {

    components: { PublishField },

    inject: ['storeName'],

    props: {
        fields: {
            type: Array,
            required: true
        }
    },

    computed: {

        state() {
            return this.$store.state.publish[this.storeName];
        },

        values() {
            return this.state.values;
        },

        meta() {
            return this.state.meta;
        },

        errors() {
            return this.state.errors;
        }

    },

    methods: {

        updated(handle, value) {
            this.$store.dispatch(`publish/${this.storeName}/setValue`, { handle, value });
        }

    }

}
</script>
