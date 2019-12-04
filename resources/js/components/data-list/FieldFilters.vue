<template>

    <div class="p-3">

        <div v-if="hasFilters" v-for="(filter, index) in filters">
            <div class="flex items-center mb-3">

                <select-input
                    class="mr-2"
                    name="fieldHandle"
                    :options="fieldOptions(filter)"
                    :value="filter.fieldHandle"
                    :placeholder="__('Select field')"
                    @input="rekey(index, $event)" />

                <field-filter
                    :filter="filter"
                    :operators="fieldOperators(filter)"
                    class="flex-1"
                    @updated="filterUpdated(index, $event)" />

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

        canAdd() {
            return this.filters.length < this.fieldCount;
        },

    },

    created() {
        this.$events.$on('filters-reset', this.resetAll);
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

        add() {
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
