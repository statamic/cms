<template>

    <div class="w-full">
        <publish-container
            v-if="filter.fields.length"
            :name="`filter-${filter.handle}`"
            :blueprint="fieldset"
            :values="values || defaultValues"
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
        values: Object,
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
