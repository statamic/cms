<template>

    <div class="p-3">

        <field-filter
            v-for="field in selectedFields"
            :key="field.handle"
            :field="field"
            :value="values[field.handle]"
            @updated="updateField(field.handle, $event)"
            @removed="removeField(field.handle)"
        />

        <div class="border-b mb-3 pb-3" v-show="creating">

            <v-select
                class="inline-block min-w-120"
                label="display"
                name="createFilter"
                @input="selectField(handle)"
                :clearable="false"
                :placeholder="__('Select Field')"
                :options="unselectedFields"
                :searchable="false" />

            <!-- <button
                v-for="field in unselectedFields"
                :key="field.handle"
                class="btn btn-flat mt-1 mr-1 text-xs"
                @click="selectField(field.handle)"
                v-text="field.display" /> -->

        </div>

        <div class="" v-show="unselectedFields.length">
            <button class="btn" v-text="__('Add Filter')" @click="creating = true" />
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
            creating: false,
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

    created() {
        this.$events.$on('filters-reset', this.removeAll);
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
        },

        removeAll() {
            this.values = {};
        }

    }

}
</script>
