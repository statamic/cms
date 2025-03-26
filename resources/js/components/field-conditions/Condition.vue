<template>
    <div class="flex flex-wrap items-center border-t py-4 dark:border-dark-900">
        <div v-if="index === 0" class="help-block" v-text="__('messages.field_conditions_field_instructions')" />

        <v-select
            ref="fieldSelect"
            :model-value="condition.field"
            class="mb-2 w-full md:mb-0 md:w-1/3"
            :options="fieldOptions"
            :placeholder="__('Field')"
            :taggable="true"
            :push-tags="true"
            :reduce="(field) => field.value"
            :create-option="(field) => ({ value: field, label: field })"
            @update:model-value="fieldSelected"
            @-search:blur="fieldSelectBlur"
        >
            <template #no-options><div class="hidden" /></template>
            <template #option="option">
                <div class="flex items-center">
                    <span v-text="option.label" />
                    <span
                        v-text="option.value"
                        class="font-mono text-2xs text-gray-500 dark:text-dark-150"
                        :class="{ 'ml-2': option.label }"
                    />
                </div>
            </template>
        </v-select>

        <select-input
            :model-value="condition.operator"
            :options="operatorOptions"
            :placeholder="false"
            class="md:ltr:ml-4 md:rtl:mr-4"
            @update:model-value="operatorSelected"
        />

        <toggle-input
            v-if="showValueToggle"
            class="ltr:ml-4 rtl:mr-4"
            :model-value="condition.value === 'true'"
            @update:model-value="valueUpdated"
        />

        <v-select
            v-else-if="showValueDropdown"
            ref="valueSelect"
            :model-value="condition.value"
            class="mb-2 w-full md:mb-0 md:w-52 ltr:ml-4 rtl:mr-4"
            :options="valueOptions"
            :placeholder="__('Option')"
            :taggable="false"
            :push-tags="true"
            :reduce="(field) => field.value"
            :create-option="(field) => ({ value: field, label: field })"
            @update:model-value="valueUpdated"
            @-search:blur="valueSelectBlur"
        >
            <template #no-options><div class="hidden" /></template>
        </v-select>

        <text-input
            v-else
            :model-value="condition.value"
            class="ltr:ml-4 rtl:mr-4"
            @update:model-value="valueUpdated"
        />

        <button @click="remove" class="btn-close group ltr:ml-2 rtl:mr-2">
            <svg-icon name="micro/trash" class="h-4 w-4 group-hover:text-red-500" />
        </button>
    </div>
</template>

<script>
import HasInputOptions from '../fieldtypes/HasInputOptions.js';

export default {
    mixins: [HasInputOptions],

    props: {
        config: {
            type: Object,
            required: true,
        },
        condition: {
            type: Object,
            required: true,
        },
        conditions: {
            type: Array,
            required: true,
        },
        index: {
            type: Number,
            required: true,
        },
        suggestableFields: {
            type: Array,
            required: true,
        },
    },

    computed: {
        field() {
            return this.suggestableFields.find((field) => field.handle === this.condition.field);
        },

        showValueToggle() {
            return (
                this.field &&
                ['toggle', 'revealer'].includes(this.field.config.type) &&
                ['equals', 'not', '===', '!=='].includes(this.condition.operator)
            );
        },

        showValueDropdown() {
            return (
                this.field &&
                ['button_group', 'checkboxes', 'radio', 'select'].includes(this.field.config.type) &&
                ['equals', 'not', '===', '!=='].includes(this.condition.operator)
            );
        },

        valueOptions() {
            if (!this.showValueDropdown) return;

            return this.normalizeInputOptions(this.field.config.options);
        },

        fieldOptions() {
            const conditions = this.conditions.map((condition) => condition.field);

            return this.suggestableFields
                .filter((field) => {
                    return !(
                        field.handle === this.config.handle || // Exclude the field you're adding a condition to.
                        this.condition.field === field.handle || // Exclude the field being used in the current condition.
                        conditions.includes(field.handle)
                    ); // Exclude fields already used in other conditions.
                })
                .map((field) => {
                    let display = field.config.display;

                    if (!display) {
                        display = field.handle.replace(/_/g, ' ').replace(/(?:^|\s)\S/g, function (a) {
                            return a.toUpperCase();
                        });
                    }

                    return { value: field.handle, label: display };
                });
        },

        operatorOptions() {
            return this.normalizeInputOptions({
                equals: __('equals'),
                not: __('not'),
                contains: __('contains'),
                contains_any: __('contains any'),
                '===': '===',
                '!==': '!==',
                '>': '>',
                '>=': '>=',
                '<': '<',
                '<=': '<=',
                custom: __('custom'),
            });
        },
    },

    methods: {
        fieldSelected(field) {
            this.$emit('updated', {
                ...this.condition,
                field: field,
            });
        },

        fieldSelectBlur() {
            const value = this.$refs.fieldSelect.$refs.search.value;
            if (value) this.fieldUpdated(value);
        },

        operatorSelected(operator) {
            this.$emit('updated', {
                ...this.condition,
                operator: operator,
            });
        },

        valueUpdated(value) {
            this.$emit('updated', {
                ...this.condition,
                value: value.toString(),
            });
        },

        valueSelectBlur() {
            const value = this.$refs.valueSelect.$refs.search.value;
            if (value) this.valueUpdated(value);
        },

        remove() {
            this.$emit('removed');
        },
    },
};
</script>
