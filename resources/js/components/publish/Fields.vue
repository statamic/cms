<template>

    <div class="publish-fields">

        <publish-field
            v-for="field in fields"
            :key="field.handle"
            :config="field"
            :value="values[field.handle]"
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

        values() {
            return this.$store.state.publish[this.storeName].values;
        }

    },

    methods: {

        updated(handle, value) {
            this.$store.dispatch(`publish/${this.storeName}/updateField`, { handle, value });
        }

    }

}
</script>
