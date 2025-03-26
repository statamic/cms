<template>
    <div class="publish-fields @container">
        <publish-field
            v-for="field in fields"
            v-show="showField(field)"
            :key="field.handle"
            :config="field"
            :model-value="values[field.handle]"
            :meta="meta[field.handle]"
            :errors="errors[field.handle]"
            :read-only="readOnly"
            :syncable="isSyncableField(field)"
            :name-prefix="namePrefix"
            @update:model-value="$emit('updated', field.handle, $event)"
            @meta-updated="$emit('meta-updated', field.handle, $event)"
            @synced="$emit('synced', field.handle)"
            @desynced="$emit('desynced', field.handle)"
            @focus="$emit('focus', field.handle)"
            @blur="$emit('blur-sm', field.handle)"
        />
    </div>
</template>

<script>
import PublishField from './Field.vue';
import { ValidatesFieldConditions } from '../field-conditions/FieldConditions.js';

export default {
    emits: ['updated', 'meta-updated', 'synced', 'desynced', 'focus', 'blur-sm'],

    components: { PublishField },

    mixins: [ValidatesFieldConditions],

    inject: ['publishContainer', 'store'],

    props: {
        fields: {
            type: Array,
            required: true,
        },
        readOnly: Boolean,
        syncable: Boolean,
        syncableFields: Array,
        namePrefix: String,
    },

    computed: {
        values() {
            return this.store.values || {};
        },

        extraValues() {
            return this.store.extraValues || {};
        },

        meta() {
            return this.store.meta || {};
        },

        errors() {
            return this.store.errors || {};
        },
    },

    methods: {
        isSyncableField(field) {
            if (!this.syncable) return false;

            if (!this.syncableFields) return true;

            return this.syncableFields.includes(field.handle);
        },
    },
};
</script>
