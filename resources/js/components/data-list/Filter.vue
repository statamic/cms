<template>

    <div>
        <publish-container
            v-if="filter.fields.length"
            :name="`filter-${filter.handle}`"
            :fieldset="fieldset"
            :values="values"
            :meta="filter.meta"
            :errors="errors"
            @updated="$emit('changed', $event)"
        >
            <publish-fields slot-scope="{ setValue }" :fields="filter.fields" @updated="setValue" />
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
            values: this.initialValues || {},
            errors: {},
        }
    },

    watch: {

        value(value) {
            this.$emit('changed', value);
        }

    }

}
</script>
