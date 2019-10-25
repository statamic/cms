<template>

    <div>
        <publish-container
            v-if="filter.fields.length"
            :name="`filter-${filter.handle}`"
            :blueprint="fieldset"
            :values="values"
            :meta="filter.meta"
            :errors="errors"
            @updated="$emit('changed', $event)"
        >
            <publish-fields slot-scope="{ setFieldValue }" :fields="filter.fields" @updated="setFieldValue" />
        </publish-container>
    </div>

</template>

<script>
import PublishFields from '../publish/Fields.vue';

export default {

    components: {
        PublishFields,
    },

    props: {
        filter: Object,
        initialValues: Object
    },

    data() {
        return {
            fieldset: {sections:[{fields:this.filter.fields}]},
            values: this.initialValues || this.filter.values || {},
            errors: {},
        }
    },

    watch: {

        initialValues() {
            this.values = this.initialValues || {};
        },

        value: {
            deep: true,
            handler(value) {
                this.$emit('changed', value);
            }
        }

    }

}
</script>
