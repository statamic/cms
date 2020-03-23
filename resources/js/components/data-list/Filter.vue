<template>

    <div class="w-full no-label">
        <publish-container
            v-if="filter.fields.length"
            :name="`filter-${filter.handle}`"
            :meta="{}"
            :values="containerValues"
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
            class="mt-2 text-xs text-blue hover:text-grey-80"
            v-text="__('Clear')"
            @click="resetAll"
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
        defaultValues() {
            return this.filter.values || {};
        },

        containerValues() {
            return clone(this.values || this.defaultValues);
        },
    },

    methods: {
        resetAll() {
            this.$emit('changed', null);
            this.$emit('cleared');
        },
    },

}
</script>
