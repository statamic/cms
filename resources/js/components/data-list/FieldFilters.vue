<template>

    <div class="p-3">

        <div v-if="selectedFields.length">
            <field-filter
                v-for="field in selectedFields"
                :key="field.handle"
                :field="field"
                :value="values[field.handle]"
                class="mb-3"
                @updated="updateField(field.handle, $event)"
                @removed="removeField(field.handle)" />
        </div>

        <div v-show="unselectedFields.length" class="mt-2">
            <button
                v-text="__('Add Filter')"
                v-if="! adding"
                @click="adding = true"
                class="btn" />

            <select-input
                v-if="adding"
                :options="fieldOptions"
                :placeholder="__('Select Field')"
                :value="null"
                class="w-1/5"
                @input="add" />
        </div>

    </div>

</template>

<script>
import FieldFilter from './FieldFilter.vue';

export default {

    components: {
        FieldFilter,
    },

    props: {
        filter: {},
        initialValue: {
            default() {
                return {};
            }
        }
    },

    data() {
        return {
            values: this.initialValue,
            adding: false,
        }
    },

    computed: {

        fields() {
            return this.filter.extra;
        },

        selectedFields() {
            return this.fields.filter(field => this.values.hasOwnProperty(field.handle));
        },

        unselectedFields() {
            return this.fields.filter(field => !this.values.hasOwnProperty(field.handle));
        },

        fieldOptions() {
            return this.unselectedFields.map(field => {
                return {
                    value: field.handle,
                    label: field.display
                };
            });
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
        this.$events.$on('filters-reset', this.removeAll);
    },

    methods: {

        add(handle) {
            this.selectField(handle);

            this.adding = false;
        },

        selectField(handle) {
            this.updateField(handle, { value: '', operator: '=' });
        },

        updateField(handle, value) {
            Vue.set(this.values, handle, value);
        },

        removeField(handle) {
            Vue.delete(this.values, handle);
        },

        removeAll() {
            this.values = {};
        }

    }

}
</script>
