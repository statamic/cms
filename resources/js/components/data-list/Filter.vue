<template>

    <div class="w-full no-label">
        <publish-container
            class="p-3"
            v-if="filter.fields.length"
            :name="`filter-${filter.handle}`"
            :meta="{}"
            :values="containerValues"
            :track-dirty-state="false"
            @updated="updateValues"
        >
            <publish-fields
                slot-scope="{ setFieldValue }"
                :fields="filter.fields"
                :name-prefix="`filter-${filter.handle}`"
                @updated="setFieldValue"
            />
        </publish-container>

        <div class="flex border-t">
            <button
                class="p-2 hover:bg-gray-100 rtl:rounded-br ltr:rounded-bl text-xs flex-1"
                v-text="__('Clear')"
                @click="resetAll"
            />
            <button
                class="p-2 hover:bg-gray-100 flex-1 rtl:rounded-bl ltr:rounded-br rtl:border-r ltr:border-l text-xs"
                v-text="__('Close')"
                @click="$emit('closed')"
            />
        </div>
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
        updateValues(values) {
            let filteredValues = clone(values);

            Object.keys(values).forEach(key => {
                if (_.isEmpty(values[key])) delete filteredValues[key];
            });

            this.$emit('changed', filteredValues);
        },

        resetAll() {
            this.$emit('changed', null);
            this.$emit('cleared');
        },

        close() {
            this.$emit('closed');
        }
    },

}
</script>
