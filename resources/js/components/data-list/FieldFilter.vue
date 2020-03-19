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
                <div v-if="operator" class="single-field">
                    <template v-for="(config, handle) in filter.config">
                        <publish-field
                            ref="valueField"
                            :config="config"
                            name-prefix="field-filter"
                            :name="`${field}-${handle}`"
                            :handle="`${field}-${handle}`"
                            class="single-field w-full mt-1"
                            :value="valuesPayload[handle] || null"
                            @input="updateValuesPayload(handle, $event)"
                        />
                    </template>
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
            valuesPayload: null,
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
            let value = this.valuesPayload.value || null; // TODO: Handle multiple values from `filterValueConfig()`

            return this.field !== null && this.operator !== null && value !== null;
        },

        newValues() {
            let values = clone(this.values);

            delete values[this.field];

            if (this.isFilterComplete) {
                values[this.field] = {
                    operator: this.operator,
                    values: this.valuesPayload,
                };
            }

            return values;
        },

    },

    watch: {
        field: 'update',
        operator: 'update',
        valuesPayload: {
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
            this.initialValues = this.values;
            this.filter = null;
            this.field = null;
            this.operator = null;
            this.valuesPayload = {};
        },

        createFilter(field) {
            if (this.field) this.$emit('changed', this.initialValues);

            this.reset();
            this.filter = _.find(this.availableFilters, filter => filter.handle === field);
            this.field = field;

            if (this.showOperators)
                this.$nextTick(() => this.$refs.operatorSelect.$refs.search.focus());
            else
                this.autoselectOperator();
        },

        autoselectOperator() {
            this.operator = this.operatorOptions[0].value;

            this.focusValueField();
        },

        updateOperator(operator) {
            this.operator = operator;

            this.focusValueField();
        },

        focusValueField() {
            // TODO: When fieldtype has a reliable `.focus()` method...
            // this.$nextTick(() => {
            //     this.$refs.valueField.focus();
            // });
        },

        updateValuesPayload: _.debounce(function (handle, value) {
            Vue.set(this.valuesPayload, handle, value);
        }, 300),

        update() {
            if (this.isFilterComplete) this.$emit('changed', this.newValues);
        },

    }

}
</script>
