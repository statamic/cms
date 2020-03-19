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
                <!-- TODO: handle showing/hiding of labels more elegantly -->
                <div v-if="showFieldFilter" class="mt-1">
                    <template v-for="filterField in filter.fields">
                        <publish-field
                            ref="valueFields"
                            :config="filterField"
                            :name-prefix="`field-filter-${field}`"
                            :name="filterField.handle"
                            :handle="filterField.handle"
                            class="w-full no-label"
                            :value="fieldValues[filterField.handle] || null"
                            @input="updateValuesPayload(filterField.handle, $event)"
                        />
                    </template>
                </div>
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
            return this.availableFieldFilters.map(filter => {
                return {
                    value: filter.handle,
                    label: filter.display,
                };
            });
        },

        showFieldFilter() {
            return this.field;
        },

        // TODO: Dynamically handle multiple values by checking `required`?
        isFilterComplete() {
            return this.field !== null
                && this.fieldValues.operator
                && this.fieldValues.value;
        },

        newValues() {
            let values = clone(this.values);

            delete values[this.field];

            if (this.isFilterComplete) {
                values[this.field] = { values: this.fieldValues };
            }

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
            this.initialValues = this.values;
            this.filter = null;
            this.field = null;
            this.fieldValues = {};
        },

        createFilter(field) {
            if (this.field) this.$emit('changed', this.initialValues);

            this.reset();
            this.filter = _.find(this.availableFieldFilters, filter => filter.handle === field);
            this.field = field;

            // TODO: When fieldtype has a reliable `.focus()` method...
            // this.$nextTick(() => {
            //     this.$refs.valueFields[0].focus();
            // });
        },

        updateValuesPayload: _.debounce(function (handle, value) {
            Vue.set(this.fieldValues, handle, value);
        }, 300),

        update() {
            if (this.isFilterComplete) this.$emit('changed', this.newValues);
        },

    }

}
</script>
