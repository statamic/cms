<template>

    <div>
        <publish-container
            v-if="filter.fields.length"
            :name="`filter-${filter.handle}`"
            :blueprint="fieldset"
            :values="values || defaultValues"
            :meta="filter.meta"
            :errors="errors"
            :track-dirty-state="false"
            @updated="$emit('changed', $event)"
        >
            <publish-fields
                slot-scope="{ setFieldValue }"
                :fields="filter.fields"
                :no-form-group="noFormGroup"
                @updated="setFieldValue" />
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
        values: Object,
        noFormGroup: Boolean,
    },

    data() {
        return {
            fieldset: {sections:[{fields:this.filter.fields}]},
            defaultValues: this.filter.values || {},
            errors: {},
        }
    },

}
</script>
