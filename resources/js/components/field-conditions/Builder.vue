<template>

    <div class="w-full">

        <div class="form-group publish-field select-fieldtype field-w-full">
            <label class="publish-field-label">{{ __('Conditions') }}</label>
            <div class="help-block -mt-1"><p>{{ __('messages.field_conditions_instructions') }}</p></div>

            <div class="flex items-center mb-3">
                <select-input
                    v-model="when"
                    :options="whenOptions"
                    :placeholder="false" />

                <select-input
                    v-if="hasConditions"
                    v-model="type"
                    :options="typeOptions"
                    :placeholder="false"
                    class="ml-2" />

                <text-input
                    v-if="hasConditions && isCustom"
                    v-model="customMethod"
                    class="ml-2 flex-1" />
            </div>

            <div
                v-if="hasConditions && isStandard"
                v-for="(condition, index) in conditions"
                :key="condition._id"
                class="flex items-center py-2 border-t"
            >
                <v-select
                    ref="fieldSelect"
                    v-model="conditions[index].field"
                    class="min-w-md"
                    :options="fieldOptions"
                    :placeholder="__('Field')"
                    :taggable="true"
                    :push-tags="true"
                    :reduce="field => field.value"
                    :create-option="field => ({value: field, label: field })"
                    @search:blur="fieldSelectBlur(index)"
                >
                    <template #no-options><div class="hidden" /></template>
                </v-select>

                <select-input
                    v-model="conditions[index].operator"
                    :options="operatorOptions"
                    :placeholder="false"
                    class="ml-2" />

                <text-input
                    v-model="conditions[index].value"
                    class="ml-2" />

                <button @click="remove(index)" class="btn-close ml-1 group">
                    <svg-icon name="trash" class="w-4 h-4 group-hover:text-red" />
                </button>
            </div>

            <div class="border-t pt-3" v-if="hasConditions && isStandard">
                <button
                    v-text="__('Add Condition')"
                    @click="add"
                    class="btn-default" />
            </div>

        </div>

        <div class="form-group publish-field select-fieldtype field-w-full">
            <label class="publish-field-label">{{ __('Always Save') }}</label>
            <div class="help-block -mt-1">
                <p>{{ __('messages.field_conditions_always_save_instructions') }}</p>
            </div>
            <toggle-input v-model="alwaysSave" />
        </div>

    </div>

</template>


<script>
import uniqid from 'uniqid';
import HasInputOptions from '../fieldtypes/HasInputOptions.js';
import Converter from '../field-conditions/Converter.js';
import { KEYS, OPERATORS } from '../field-conditions/Constants.js';

export default {

    mixins: [HasInputOptions],

    props: {
        config: {
            required: true
        },
        suggestableFields: {
            required: true
        }
    },

    data() {
        return {
            when: 'always',
            type: 'all',
            customMethod: null,
            conditions: [],
            alwaysSave: false,
        }
    },

    computed: {

        whenOptions() {
            return this.normalizeInputOptions({
                always: __('Always show'),
                if: __('Show when'),
                unless: __('Hide when')
            });
        },

        typeOptions() {
            return this.normalizeInputOptions({
                all: __('All of the following conditions pass'),
                any: __('Any of the following conditions pass'),
                custom: __('Custom method passes')
            });
        },

        fieldOptions() {
            return this.normalizeInputOptions(
                _.reject(this.suggestableFields, field => field === this.config.handle)
            );
        },

        operatorOptions() {
            return this.normalizeInputOptions(OPERATORS);
        },

        hasConditions() {
            return this.when !== 'always';
        },

        isStandard() {
            return this.hasConditions && ! this.isCustom;
        },

        isCustom() {
            return this.type === 'custom';
        },

        saveableConditions() {
            var conditions = {};
            let key = this.type === 'any' ? `${this.when}_any` : this.when;
            let saveableConditions = this.prepareSaveableConditions(this.conditions);

            if (this.isStandard && ! _.isEmpty(saveableConditions)) {
                conditions[key] = saveableConditions;
            } else if (this.isCustom && this.customMethod) {
                conditions[key] = this.customMethod;
            }

            return conditions;
        }

    },

    watch: {

        saveableConditions: {
            deep: true,
            handler(conditions) {
                this.$emit('updated', conditions);
            }
        },

        alwaysSave(alwaysSave) {
            this.$emit('updated-always-save', alwaysSave);
        },

    },

    created() {
        this.add();
        this.getInitialConditions();
        this.getInitialAlwaysSaveState();
    },

    methods: {

        add() {
            this.conditions.push({
                _id: uniqid(),
                field: null,
                operator: 'equals',
                value: null
            });
        },

        remove(index) {
            this.conditions.splice(index, 1);
        },

        getInitialConditions() {
            let key = _.chain(KEYS)
                .filter(key => this.config[key])
                .first()
                .value();

            let conditions = this.config[key];

            if (! conditions) {
                return;
            }

            this.when = key.startsWith('unless') || key.startsWith('hide_when')
                ? 'unless'
                : 'if';

            this.type = key.endsWith('_any')
                ? 'any'
                : 'all';

            if (typeof conditions === 'string') {
                this.type = 'custom';
                this.customMethod = conditions;
                return;
            }

            this.conditions = this.prepareEditableConditions(conditions);
        },

        getInitialAlwaysSaveState() {
            this.alwaysSave = data_get(this.config, 'always_save', false);
        },

        prepareEditableConditions(conditions) {
            return (new Converter).fromBlueprint(conditions).map(condition => {
                condition._id = uniqid();
                condition.operator = this.prepareEditableOperator(condition.operator);
                return condition;
            });
        },

        prepareSaveableConditions(conditions) {
            conditions = _.reject(conditions, condition => {
                return ! condition.field || ! condition.value;
            });

            return (new Converter).toBlueprint(conditions);
        },

        prepareEditableOperator(operator) {
            switch (operator) {
                case 'is':
                case '==':
                    return '';
                case 'isnt':
                case '!=':
                    return 'not';
            }

            return operator;
        },

        fieldSelectBlur(index) {
            const value = this.$refs.fieldSelect[index].$refs.search.value;
            if (value) this.conditions[index].field = value;
        },

    }
}
</script>
