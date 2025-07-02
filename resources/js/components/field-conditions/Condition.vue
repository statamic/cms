<template>
    <div class="dark:border-dark-900 flex flex-wrap items-center space-x-4 border-t py-4">
        <Description
            v-if="index === 0"
            class="mb-4 w-full"
            :text="__('messages.field_conditions_field_instructions')"
        />

        <div class="mb-2 w-full md:mb-0 md:w-1/3">
            <Combobox
                ref="fieldSelect"
                :model-value="condition.field"
                class="w-full"
                :options="fieldOptions"
                :placeholder="__('Field')"
                :taggable="true"
                @update:modelValue="fieldSelected"
                search:blur="fieldSelectBlur"
            >
                <template #no-options><div class="hidden" /></template>
                <template #option="option">
                    <div class="flex items-center">
                        <span v-text="option.label" />
                        <span
                            v-text="option.value"
                            class="text-2xs dark:text-dark-150 font-mono text-gray-500"
                            :class="{ 'ml-2': option.label }"
                        />
                    </div>
                </template>
                <template #selected-option>
                    <span v-text="__(field.config.display) || field.handle"></span>
                </template>
            </Combobox>
        </div>

        <div class="w-32">
            <Select
                class="w-full"
                :model-value="condition.operator"
                :options="operatorOptions"
                @update:model-value="operatorSelected"
            />
        </div>

        <Switch v-if="showValueToggle" :model-value="condition.value === 'true'" @update:model-value="valueUpdated" />

        <Combobox
            v-else-if="showValueDropdown"
            ref="valueSelect"
            :model-value="condition.value"
            class="mb-2 w-full md:mb-0 md:w-52"
            :options="valueOptions"
            :placeholder="__('Option')"
            :taggable="false"
            @update:model-value="valueUpdated"
            search:blur="valueSelectBlur"
        >
            <template #no-options><div class="hidden" /></template>
        </Combobox>

        <Input v-else class="flex-1" :model-value="condition.value" @update:model-value="valueUpdated" />

        <Button variant="ghost" size="sm" icon="trash" @click="remove" />
    </div>
</template>

<script>
import HasInputOptions from '../fieldtypes/HasInputOptions.js';
import { Description, Combobox, Input, Button } from '@statamic/ui';
import Select from '@statamic/components/ui/Select/Select.vue';
import Switch from '@statamic/components/ui/Switch.vue';

export default {
    mixins: [HasInputOptions],

    components: { Description, Combobox, Input, Button, Select, Switch },

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
