<template>
    <div>
        <div v-if="hasAvailableFilters">
            <div class="flex flex-col">
                <v-select
                    ref="fieldSelect"
                    :placeholder="__('Select Field')"
                    :options="fieldOptions"
                    :reduce="option => option.value"
                    :value="field"
                    @input="createFilter"
                />
                <v-select
                    v-show="showOperators"
                    ref="operatorSelect"
                    class="w-full mt-1"
                    :placeholder="__('Select Operator')"
                    :options="operatorOptions"
                    :reduce="option => option.value"
                    :value="operator"
                    @input="updateOperator"
                />
                <div class="single-field">
                    <publish-field
                        v-if="operator"
                        ref="valueField"
                        :config="filter.config"
                        :handle="field"
                        name-prefix="field-filter"
                        :name="field"
                        class="single-field w-full mt-1"
                        :value="value"
                        @input="updateValue"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import PublishField from '../publish/Field.vue';
import HasInputOptions from '../fieldtypes/HasInputOptions.js';

export default {

    mixins: [HasInputOptions],

    components: { PublishField },

    props: {
        config: Object,
        values: Object,
    },

    data() {
        return {
            initialValues: this.values,
            filter: null,
            field: null,
            operator: null,
            value: null,
        };
    },

    computed: {

        availableFilters() {
            return this.config.extra.filter(field => ! this.initialValues[field.handle]);
        },

        hasAvailableFilters() {
            return !! this.availableFilters.length;
        },

        fieldOptions() {
            return this.availableFilters.map(filter => {
                return {
                    value: filter.handle,
                    label: filter.display,
                };
            });
        },

        operatorOptions() {
            if (! this.filter) return [];

            return this.normalizeInputOptions(this.filter.operators);
        },

        showOperators() {
            return this.operatorOptions.length > 1;
        },

        isFilterComplete() {
            return this.field !== null && this.operator !== null && this.value !== null;
        },

        newValues() {
            let values = clone(this.values);

            delete values[this.field];

            if (this.isFilterComplete) {
                values[this.field] = {
                    operator: this.operator,
                    value: this.value,
                };
            }

            return values;
        },

    },

    watch: {
        field: 'update',
        operator: 'update',
        value: 'update',
    },

    mounted() {
        this.reset();

        this.$refs.fieldSelect.$refs.search.focus();
    },

    methods: {

        reset() {
            this.initialValues = this.values;
            this.filter = null;
            this.field = null;
            this.operator = null;
            this.value = null;
        },

        createFilter(field) {
            if (this.field) this.$emit('changed', this.initialValues);

            this.reset();
            this.filter = _.find(this.availableFilters, filter => filter.handle === field);
            this.field = field;

            if (! this.showOperators) this.autoSelectFirstOperator();

            this.$nextTick(() => {
                this.$refs.operatorSelect.$refs.search.focus();
            });
        },

        autoSelectFirstOperator() {
            this.operator = this.operatorOptions[0].value;
        },

        updateOperator(operator) {
            this.operator = operator;

            // TODO: When fieldtype has a reliable `.focus()` method...
            // this.$nextTick(() => {
            //     this.$refs.valueField.focus();
            // });
        },

        updateValue: _.debounce(function (value) {
            this.value = value;
        }, 300),

        update() {
            if (this.isFilterComplete) this.$emit('changed', this.newValues);
        },

    }

}
</script>
