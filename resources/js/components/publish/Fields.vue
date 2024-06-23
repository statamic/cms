<template>

    <div class="publish-fields @container">

        <template
            v-for="field in fields"
            :key="field.handle"
        >
            <publish-field
                v-show="showField(field)"
                :config="field"
                :value="values[field.handle]"
                :meta="meta[field.handle]"
                :errors="errors[field.handle]"
                :read-only="readOnly"
                :syncable="isSyncableField(field)"
                :name-prefix="namePrefix"
                @input="$emit('updated', field.handle, $event)"
                @meta-updated="$emit('meta-updated', field.handle, $event)"
                @synced="$emit('synced', field.handle)"
                @desynced="$emit('desynced', field.handle)"
                @focus="$emit('focus', field.handle)"
                @blur="$emit('blur', field.handle)"
            />
        </template>

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
        syncable: Boolean,
        syncableFields: Array,
        namePrefix: String,
    },

    computed: {

        state() {
            console.log(this.$store.state.publish);
            return this.$store.state.publish[this.storeName];
        },

        values() {
            console.log(this.state);
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
