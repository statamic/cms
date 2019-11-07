<template>

    <div class="p-3">

        <div v-if="hasFilters" v-for="(filter, index) in filters">
            <div class="flex items-center mb-3">

                <select-input
                    class="mr-2"
                    name="operator"
                    :options="fieldOptions(filter)"
                    v-model="filter.field"
                    :placeholder="__('Select field')"
                    @input="reset(index, $event)" />

                <field-filter
                    :filter="filter"
                    :operators="fieldOperators(filter)"
                    class="flex-1"
                    @updated="update(index, $event)" />

                <button @click="remove(index)" class="btn-close ml-1 group">
                    <svg-icon name="trash" class="w-auto group-hover:text-red" />
                </button>

            </div>
        </div>
        <div :class="{ 'border-t': hasFilters, 'pt-3': hasFilters }">
            <button
                v-text="__('Add Filter')"
                class="btn"
                :disabled="! canAdd"
                @click="add()" />
        </div>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import FieldFilter from './FieldFilter.vue';

export default {

    components: {
        FieldFilter,
    },

    props: {
        filter: {},
        initialFilters: {
            default() {
                return {};
            }
        }
    },

    data() {
        return {
            filters: [],
        }
    },

    computed: {

        fields() {
            let fields = {};

            this.filter.extra.forEach(field => {
                fields[field.handle] = field;
            });

            return fields;
        },

        fieldCount() {
            return Object.keys(this.fields).length;
        },

        unselectedFieldOptions() {
            let fields = _.map(this.fields, (field, handle) => handle);
            let selectedFields = this.filters.map(filter => filter.field);

            return fields
                .filter(field => ! selectedFields.includes(field))
                .map(field => {
                    return {
                        value: this.fields[field].handle,
                        label: this.fields[field].display
                    };
                });
        },

        hasFilters() {
            return this.filters.length;
        },

        incompleteFilters() {
            return this.filters.filter(filter => ! this.isFilterComplete(filter));
        },

        canAdd() {
            return this.filters.length < this.fieldCount;
        },

        values() {
            let values = {};

            this.filters
                .filter(filter => this.isFilterComplete(filter))
                .forEach(filter => {
                    values[filter.field] = {
                        operator: filter.operator,
                        value: filter.value
                    };
                });

            return values;
        }

    },

    watch: {

        values: {
            deep: true,
            handler(values) {
                this.$emit('changed', values);
            }
        }

    },

    created() {
        this.setInitialFilters();

        this.$events.$on('filters-reset', this.resetAll);
    },

    methods: {

        setInitialFilters() {
            _.each(this.initialFilters, (filter, field) => {
                this.add(field, filter.operator, filter.value);
            });

            if (this.filters.length === 0) {
                this.add();
            }
        },

        fieldOptions(filter) {
            if (! filter.field) {
                return this.unselectedFieldOptions;
            }

            return [{
                value: filter.field,
                label: this.fields[filter.field].display
            }].concat(this.unselectedFieldOptions);
        },

        fieldOperators(filter) {
            return filter.field ? this.fields[filter.field].operators : {};
        },

        isFilterComplete(filter) {
            return filter.field !== null && filter.operator !== null && filter.value;
        },

        add(handle=null, operator=null, value=null) {
            this.filters.push({
                _id: uniqid(),
                field: handle,
                operator,
                value
            });
        },

        update(index, event) {
            this.filters[index].operator = event.operator;
            this.filters[index].value = event.value;
        },

        reset(index) {
            this.filters[index].operator = null;
            this.filters[index].value = null;
        },

        remove(index) {
            this.filters.splice(index, 1);
        },

        resetAll() {
            this.filters = [];
        }

    }

}
</script>
