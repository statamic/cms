<template>
    <div class="no-label w-full">
        <publish-container
            class="p-3"
            v-if="filter.fields.length"
            :name="`filter-${filter.handle}`"
            :meta="meta"
            :values="containerValues"
            :track-dirty-state="false"
            @updated="updateValues"
            v-slot="{ setFieldValue }"
        >
            <publish-fields
                :fields="filter.fields"
                :name-prefix="`filter-${filter.handle}`"
                @updated="setFieldValue"
                @meta-updated="updateMeta"
            />
        </publish-container>

        <div class="dark:border-dark-900 flex border-t">
            <button
                class="dark:hover:bg-dark-600 flex-1 p-2 text-xs hover:bg-gray-100 ltr:rounded-bl rtl:rounded-br"
                v-text="__('Clear')"
                @click="resetAll"
            />
            <button
                class="dark:border-dark-900 dark:hover:bg-dark-600 flex-1 p-2 text-xs hover:bg-gray-100 ltr:rounded-br ltr:border-l rtl:rounded-bl rtl:border-r"
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

    data() {
        return {
            meta: {},
        };
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

            Object.keys(values).forEach((key) => {
                if (values[key] === null || values[key] === undefined) delete filteredValues[key];
            });

            this.$emit('changed', filteredValues);
        },

        resetAll() {
            this.$emit('changed', null);
            this.$emit('cleared');
        },

        updateMeta(value) {
            this.meta = value;
        },

        close() {
            this.$emit('closed');
        },
    },
};
</script>
