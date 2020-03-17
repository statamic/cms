<template>
    <div>
        <div v-if="hasAvailableFilters">
            <div class="flex flex-col">
                <select-input
                    name="fieldHandle"
                    :placeholder="__('Select Field')"
                    :options="fieldOptions"
                    :value="field"
                    @input="createFilter"
                />
                <select-input
                    v-if="operatorOptions"
                    class="w-full mt-1"
                    :placeholder="__('Select Operator')"
                    :options="operatorOptions"
                    v-model="operator"
                />
                <text-input
                    v-if="operator"
                    class="w-full mt-1"
                    :value="value"
                    @input="updateValue"
                />
            </div>
        </div>
    </div>
</template>

<script>
import HasInputOptions from '../fieldtypes/HasInputOptions.js';

export default {

    mixins: [HasInputOptions],

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
            if (! this.filter) return false;

            return this.normalizeInputOptions(this.filter.operators);
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
