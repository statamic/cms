<template>
    <div>
        <div v-if="hasAvailableFieldFilters">
            <div class="flex flex-col">

                <v-select
                    ref="fieldSelect"
                    :placeholder="__('Select Field')"
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
                    class="filter-fields mt-1"
                    @updated="updateValues"
                >
                    <!-- TODO: handle showing/hiding of labels more elegantly -->
                    <publish-fields
                        slot-scope="{ setFieldValue }"
                        :fields="filter.fields"
                        name-prefix="filter-field"
                        class="w-full no-label"
                        @updated="setFieldValue"
                    />
                </publish-container>

            </div>

            <button
                class="outline-none mt-2 text-xs text-blue hover:text-grey-80"
                v-text="__('Clear')"
                @click="reset"
            />

        </div>
    </div>
</template>

<script>
import PublishField from '../publish/Field.vue';

export default {

    components: { PublishField },

    props: {
        config: Object,
        values: Object,
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

            let fields = _.chain(this.filter.fields).mapObject(field => field.handle).values().value();
            let allFieldsFilled = _.values(this.fieldValues).filter(value => value).length === fields.length;

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
        this.reset();

        this.$refs.fieldSelect.$refs.search.focus();
    },

    methods: {

        reset() {
            if (this.field) this.$emit('changed', this.initialValues);

            this.containerValues = {};
            this.filter = null;
            this.field = null;
            this.fieldValues = null;
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
