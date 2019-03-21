<template>

    <div>
        <field-filter
            v-for="field in selectedFields"
            :key="field.handle"
            :field="field"
            :value="values[field.handle]"
            class="mb-3"
            @updated="updateField(field.handle, $event)"
            @removed="removeField(field.handle)"
        />

        <h6 v-show="unselectedFields.length">Add filter...</h6>

        <button
            v-for="field in unselectedFields"
            :key="field.handle"
            class="btn btn-flat mt-1 mr-1 text-xs"
            @click="selectField(field.handle)"
            v-text="field.display" />
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

    methods: {

        selectField(handle) {
            this.updateField(handle, { value: '', operator: '=' });
        },

        updateField(handle, value) {
            Vue.set(this.values, handle, value);
        },

        removeField(handle) {
            Vue.delete(this.values, handle);
        }

    }

}
</script>
