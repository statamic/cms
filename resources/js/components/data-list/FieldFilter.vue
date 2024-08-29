<template>
    <div>
        <div v-if="hasAvailableFieldFilters">
            <div class="flex flex-col p-3">

                <v-select
                    ref="fieldSelect"
                    :placeholder="__('Field')"
                    :options="fieldOptions"
                    :reduce="option => option.value"
                    :value="field"
                    @input="createFilter"
                />

                <publish-container
                    v-if="showFieldFilter"
                    name="filter-field"
                    :meta="{}"
                    :values="containerValues"
                    :track-dirty-state="false"
                    class="filter-fields mt-2"
                    @updated="updateValues"
                >
                    <!-- TODO: handle showing/hiding of labels more elegantly -->
                    <publish-fields
                        slot-scope="{ setFieldValue, setFieldMeta }"
                        :fields="filter.fields"
                        name-prefix="filter-field"
                        class="w-full no-label"
                        @updated="setFieldValue"
                        @meta-updated="setFieldMeta"
                    />
                </publish-container>

            </div>

            <div class="flex border-t dark:border-dark-900 text-gray-800 dark:text-dark-150">
                <button
                    class="p-2 hover:bg-gray-100 dark:hover:bg-dark-600 rtl:rounded-br ltr:rounded-bl text-xs flex-1"
                    v-text="__('Clear')"
                    @click="resetAll"
                />
                <button
                    class="p-2 hover:bg-gray-100 dark:hover:bg-dark-600 flex-1 rtl:rounded-bl ltr:rounded-br rtl:border-r ltr:border-l dark:border-dark-900 text-xs"
                    v-text="__('Close')"
                    @click="$emit('closed')"
                />
            </div>

        </div>
        <v-select v-else :disabled="true" :placeholder="__('No available filters')" />
    </div>
</template>

<script>
import Validator from '../field-conditions/Validator.js';
import PublishField from '../publish/Field.vue';

export default {

    components: { PublishField },

    props: {
        config: Object,
        values: Object,
        badges: Object,
    },

    data() {
        return {
            initialValues: this.values,
            containerValues: {},
            filter: null,
            field: null,
            fieldValues: null,
        };
    },

    computed: {

        availableFieldFilters() {
            if (! this.config) return [];

            return this.config.extra.filter(field => ! this.initialValues[field.handle]);
        },

        hasAvailableFieldFilters() {
            return !! this.availableFieldFilters.length;
        },

        fieldOptions() {
            let options = this.availableFieldFilters.map(filter => {
                return {
                    value: filter.handle,
                    label: filter.display,
                };
            });

            return _.sortBy(options, option => option.label);
        },

        showFieldFilter() {
            return this.field;
        },

        isFilterComplete() {
            if (! this.filter) return false;

            let visibleFields = _.chain(this.filter.fields).filter(function (field) {
                let validator = new Validator(field, this.fieldValues);
                return validator.passesConditions();
            }, this).mapObject(field => field.handle).values().value();

            let allFieldsFilled = _.chain(this.fieldValues).filter((value, handle) => visibleFields.includes(handle) && value).values().value().length === visibleFields.length;

            return this.field !== null && allFieldsFilled;
        },

        newValues() {
            let values = clone(this.values);

            delete values[this.field];

            values[this.field] = this.isFilterComplete
                ? this.fieldValues
                : null;

            return values;
        },

    },

    watch: {
        field: 'update',
        fieldValues: {
            deep: true,
            handler() {
                this.update();
            }
        },
    },

    mounted() {
        if (! this.hasAvailableFieldFilters) return;

        this.reset();

        this.$refs.fieldSelect.$refs.search.focus();
    },

    methods: {

        popoverClosed() {
            if (! this.badges[this.field]) {
                this.resetAll();
            }
        },

        reset() {
            if (this.field) this.$emit('changed', this.initialValues);

            this.containerValues = {};
            this.filter = null;
            this.field = null;
            this.fieldValues = null;

        },

        resetAll() {
            this.reset();

            this.$emit('cleared');
        },

        resetInitialValues() {
            this.initialValues = this.values;

            this.reset();
        },

        createFilter(field) {
            if (this.field) this.$emit('changed', this.initialValues);

            this.reset();
            this.setFilter(field);
            this.setDefaultValues();

            this.field = field;

            // TODO: When fieldtype has a reliable `.focus()` method...
            // this.$nextTick(() => {
            //     this.$refs.valueFields[0].focus();
            // });
        },

        setFilter(field) {
            this.filter = _.find(this.availableFieldFilters, filter => filter.handle === field);
        },

        setDefaultValues() {
            if (! this.filter) return;

            let values = {};

            this.filter.fields
                .filter(field => field.default)
                .forEach(field => values[field.handle] = field.default);

            this.updateValues(values);
        },

        updateValues(values) {
            this.updateContainerValues(values);
            this.updateFieldValues(values);
        },

        updateContainerValues(values) {
            this.containerValues = clone(values);
        },

        updateFieldValues: _.debounce(function (values) {
            this.fieldValues = clone(values);
        }, 300),

        update() {
            this.$emit('changed', this.newValues);
        },

    }

}
</script>
