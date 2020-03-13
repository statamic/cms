<template>
    <div>
        <div v-if="hasFilters" v-for="(filter, index) in filters">
            <div class="flex flex-col mb-1">
                <select-input
                    class="mb-1"
                    name="fieldHandle"
                    :placeholder="__('Select field')"
                    :options="fieldOptions(filter)"
                    :value="filter.fieldHandle"
                    @input="rekey(index, $event)"
                />
                <field-filter
                    :filter="filter"
                    :operators="fieldOperators(filter)"
                    class="flex-1"
                    @updated="filterUpdated(index, $event)"
                />
            </div>
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
        config: {},
        values: {
            default() {
                return {};
            }
        }
    },

    computed: {

        filters() {
            if (!this.values) return [];
            const filters = [];
            for (const handle in this.values) {
                filters.push({
                    fieldHandle: handle,
                    ...this.values[handle],
                });
            }
            return filters;
        },

        fields() {
            let fields = {};

            this.config.extra.forEach(field => {
                fields[field.handle] = field;
            });

            return fields;
        },

        fieldCount() {
            return Object.keys(this.fields).length;
        },

        unselectedFieldOptions() {
            const fieldHandles = this.config.extra.map((field) => field.handle);

            return fieldHandles
                .filter(fieldHandle => !this.values.hasOwnProperty(fieldHandle))
                .map(fieldHandle => ({
                    value: fieldHandle,
                    label: this.fields[fieldHandle].display
                }));
        },

        hasFilters() {
            return !!this.filters.length;
        },

        incompleteFilters() {
            return this.filters.filter(filter => ! this.isFilterComplete(filter));
        },

        // TODO: If false, disable field filter in parent dropdown?
        hasAvailableFieldFilters() {
            return this.filters.length < this.fieldCount;
        },

    },

    created() {
        this.createFilter();
    },

    methods: {

        fieldOptions(filter) {
            return [
                {
                    value: filter.fieldHandle,
                    label: this.fields[filter.fieldHandle].display,
                },
                ...this.unselectedFieldOptions,
            ];
        },

        fieldOperators(filter) {
            return filter.fieldHandle ? this.fields[filter.fieldHandle].operators : {};
        },

        isFilterComplete(filter) {
            return filter.fieldHandle !== null && filter.operator !== null && filter.value;
        },

        createFilter() {
            Vue.set(this.values, this.unselectedFieldOptions[0].value, {
                operator: null,
                value: null,
            });
        },

        filterUpdated(index, { operator, value }) {
            const handle = this.filters[index].fieldHandle;
            const filter = this.values[handle];
            filter.operator = operator;
            filter.value = value;
            Vue.set(this.values, handle, filter);
            if (this.isFilterComplete(filter)) {
                this.update();
            }
        },

        rekey(index, newHandle) {
            const handle = this.filters[index].fieldHandle;
            const filter = this.values[handle];
            Vue.delete(this.values, handle);
            Vue.set(this.values, newHandle, filter);
            if (this.isFilterComplete(filter)) {
                this.update();
            }
        },

        remove(index) {
            const handle = this.filters[index].fieldHandle;
            const filter = this.values[handle];
            Vue.delete(this.values, handle);
            if (this.isFilterComplete(filter)) {
                this.update();
            }
        },

        resetAll() {
            this.values = {};
            this.update();
        },

        update() {
            this.$emit('changed', this.values);
        }

    }

}
</script>
