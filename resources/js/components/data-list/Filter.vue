<template>

    <div class="w-full" :class="{ 'single-field': hasOnlyOneField }">
        <publish-container
            v-if="filter.fields.length"
            :name="`filter-${filter.handle}`"
            :blueprint="fieldset"
            :values="containerValues"
            :meta="filter.meta"
            :errors="errors"
            :track-dirty-state="false"
            @updated="$emit('changed', $event)"
        >
            <publish-fields
                slot-scope="{ setFieldValue }"
                :fields="filter.fields"
                :name-prefix="`filter-${filter.handle}`"
                @updated="setFieldValue"
            />
        </publish-container>

        <button
            class="outline-none mt-2 text-xs text-blue hover:text-grey-80"
            v-text="__('Clear')"
            @click="$emit('changed', null)"
        />
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

    computed: {
        containerValues() {
            return clone(this.values || this.defaultValues);
        },

        hasOnlyOneField() {
            return this.filter.fields.length === 1;
        }
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
