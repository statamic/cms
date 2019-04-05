<template>

    <div class="publish-fields">

        <publish-field
            v-for="field in fields"
            v-show="showField(field)"
            :key="field.handle"
            :config="field"
            :value="values[field.handle]"
            :meta="meta[field.handle]"
            :errors="errors[field.handle]"
            :read-only="readOnly"
            @updated="updated"
        />

    </div>

</template>

<script>
import PublishField from './Field.vue';
import { ValidatesFieldConditions } from '../field-conditions/FieldConditions.js';

export default {

    components: { PublishField },

    mixins: [ValidatesFieldConditions],

    inject: ['storeName'],

    props: {
        fields: {
            type: Array,
            required: true
        },
        readOnly: Boolean,
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
