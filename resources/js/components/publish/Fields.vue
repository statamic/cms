<template>

    <publish-fields-container>

        <publish-field
            v-for="field in fields"
            v-show="showField(field)"
            :key="field.handle"
            :config="field"
            :value="values[field.handle]"
            :meta="meta[field.handle]"
            :errors="errors[field.handle]"
            :read-only="readOnly"
            :syncable="isSyncableField(field)"
            :can-toggle-label="canToggleLabels"
            :name-prefix="namePrefix"
            @input="$emit('updated', field.handle, $event)"
            @meta-updated="$emit('meta-updated', field.handle, $event)"
            @synced="$emit('synced', field.handle)"
            @desynced="$emit('desynced', field.handle)"
            @focus="$emit('focus', field.handle)"
            @blur="$emit('blur', field.handle)"
        />

    </publish-fields-container>

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
        syncable: Boolean,
        syncableFields: Array,
        canToggleLabels: Boolean,
        namePrefix: String,
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

        isSyncableField(field) {
            if (! this.syncable) return false;

            if (! this.syncableFields) return true;

            return this.syncableFields.includes(field.handle);
        }

    }

}
</script>
