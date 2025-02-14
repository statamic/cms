<template>
    <div class="w-full">
        <div class="form-group publish-field select-fieldtype field-w-full">
            <label class="publish-field-label">{{ __('Conditions') }}</label>
            <div class="help-block -mt-2">
                <p>{{ __('messages.field_conditions_instructions') }}</p>
            </div>

            <div class="mb-6 flex items-center">
                <select-input v-model="when" :options="whenOptions" :placeholder="false" />

                <select-input
                    v-if="hasConditions"
                    v-model="type"
                    :options="typeOptions"
                    :placeholder="false"
                    class="ltr:ml-4 rtl:mr-4"
                />

                <text-input v-if="hasConditions && isCustom" v-model="customMethod" class="flex-1 ltr:ml-4 rtl:mr-4" />
            </div>

            <condition
                v-if="hasConditions && isStandard"
                v-for="(condition, index) in conditions"
                :index="index"
                :config="config"
                :condition="condition"
                :conditions="conditions"
                :key="condition._id"
                :suggestable-fields="suggestableFields"
                @updated="updated(index, $event)"
                @removed="remove(index)"
            />

            <div class="border-t pt-6 dark:border-dark-900" v-if="hasConditions && isStandard">
                <button v-text="__('Add Condition')" @click="add" class="btn-default" />
            </div>
        </div>

        <div class="form-group publish-field select-fieldtype field-w-full">
            <label class="publish-field-label">{{ __('Always Save') }}</label>
            <div class="help-block -mt-2">
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
import Condition from './Condition.vue';
import { __ } from '../../bootstrap/globals';
import { isEmpty, reject, filter, first } from 'lodash-es';

export default {
    mixins: [HasInputOptions],

    components: { Condition },

    props: {
        config: {
            required: true,
        },
        suggestableFields: {
            required: true,
        },
    },

    data() {
        return {
            when: 'always',
            type: 'all',
            customMethod: null,
            conditions: [],
            alwaysSave: false,
        };
    },

    computed: {
        whenOptions() {
            return this.normalizeInputOptions({
                always: __('Always show'),
                if: __('Show when'),
                unless: __('Hide when'),
            });
        },

        typeOptions() {
            return this.normalizeInputOptions({
                all: __('All of the following conditions pass'),
                any: __('Any of the following conditions pass'),
                custom: __('Custom method passes'),
            });
        },

        hasConditions() {
            return this.when !== 'always';
        },

        isStandard() {
            return this.hasConditions && !this.isCustom;
        },

        isCustom() {
            return this.type === 'custom';
        },

        saveableConditions() {
            var conditions = {};
            let key = this.type === 'any' ? `${this.when}_any` : this.when;
            let saveableConditions = this.prepareSaveableConditions(this.conditions);

            if (this.isStandard && !isEmpty(saveableConditions)) {
                conditions[key] = saveableConditions;
            } else if (this.isCustom && this.customMethod) {
                conditions[key] = this.customMethod;
            }

            return conditions;
        },
    },

    watch: {
        saveableConditions: {
            deep: true,
            handler(conditions) {
                this.$emit('updated', conditions);
            },
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
                value: null,
            });
        },

        remove(index) {
            this.conditions.splice(index, 1);
        },

        updated(index, condition) {
            this.conditions.splice(index, 1, condition);
        },

        getInitialConditions() {
            let key = first(filter(KEYS, (key) => this.config[key]));

            let conditions = this.config[key];

            if (!conditions) {
                return;
            }

            this.when = key.startsWith('unless') || key.startsWith('hide_when') ? 'unless' : 'if';

            this.type = key.endsWith('_any') ? 'any' : 'all';

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
            return new Converter().fromBlueprint(conditions).map((condition) => {
                condition._id = uniqid();
                condition.operator = this.prepareEditableOperator(condition.operator);
                return condition;
            });
        },

        prepareSaveableConditions(conditions) {
            conditions = reject(conditions, (condition) => {
                return !condition.field || !condition.value;
            });

            return new Converter().toBlueprint(conditions);
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
    },
};
</script>
