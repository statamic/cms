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
        initialValue: {}
    },

    data() {
        return {
            value: this.initialValue,
            fieldset: {sections:[{fields:this.filter.fields}]},
            values: {},
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
