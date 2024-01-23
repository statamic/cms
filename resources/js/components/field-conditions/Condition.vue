<template>
    <div class="flex flex-wrap items-center py-4 border-t">
        <div v-if="index === 0" class="help-block" v-text="__('messages.field_conditions_field_instructions')" />

        <v-select
            ref="fieldSelect"
            :value="condition.field"
            class="w-full md:w-1/3 mb-2 md:mb-0"
            :options="fieldOptions"
            :placeholder="__('Field')"
            :taggable="true"
            :push-tags="true"
            :reduce="field => field.value"
            :create-option="field => ({value: field, label: field })"
            @input="fieldSelected"
            @search:blur="fieldSelectBlur"
        >
            <template #no-options><div class="hidden" /></template>
            <template slot="option" slot-scope="option">
                <div class="flex items-center">
                    <span v-text="option.label" />
                    <span v-text="option.value" class="font-mono text-2xs text-gray-500" :class="{ 'ml-2': option.label }" />
                </div>
            </template>
        </v-select>

        <select-input
            :value="condition.operator"
            :options="operatorOptions"
            :placeholder="false"
            class="md:ml-4"
            @input="operatorSelected" />

        <text-input
            :value="condition.value"
            class="ml-4"
            @input="valueUpdated" />

        <button @click="remove" class="btn-close ml-2 group">
            <svg-icon name="micro/trash" class="w-4 h-4 group-hover:text-red-500" />
        </button>
    </div>
</template>

<script>
import HasInputOptions from '../fieldtypes/HasInputOptions.js';

export default {
    mixins: [HasInputOptions],

    props: {
        condition: {
            type: Object,
        },
        index: {
            type: Number,
        },
        fieldOptions: {
            type: Array,
        },
    },

    computed: {
        operatorOptions() {
            return this.normalizeInputOptions({
                'equals': __('equals'),
                'not': __('not'),
                'contains': __('contains'),
                'contains_any': __('contains any'),
                '===': '===',
                '!==': '!==',
                '>': '>',
                '>=': '>=',
                '<': '<',
                '<=': '<=',
                'custom': __('custom'),
            });
        },
    },

    methods: {
        fieldSelected(field) {
            this.$parent.conditions[this.index].field = field;
        },

        fieldSelectBlur() {
            const value = this.$refs.fieldSelect.$refs.search.value;
            if (value) this.fieldUpdated(value);
        },

        operatorSelected(operator) {
            this.$parent.conditions[this.index].operator = operator;
        },

        valueUpdated(value) {
            this.$parent.conditions[this.index].value = value;
        },

        remove() {
            this.$emit('removed');
        }
    }
}
</script>
