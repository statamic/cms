<template>
    <div class="w-full">
        <Field
            :label="__('Conditions')"
            :instructions="__('messages.field_conditions_instructions')"
        >
            <div class="mb-6 flex items-center gap-x-4">
                <Select v-model="when" :options="whenOptions" />

                <Select
                    v-if="hasConditions"
                    v-model="type"
                    :options="typeOptions"
                />

                <Input v-if="hasConditions && isCustom" v-model="customMethod" class="flex-1" />
            </div>
        </Field>

        <div class="mb-6">
            <Condition
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
                <Button :text="__('Add Condition')" @click="add" />
            </div>
        </div>

        <Field
            :label="__('Always Save')"
            :instructions="__('messages.field_conditions_always_save_instructions')"
        >
            <Switch v-model="alwaysSave" />
        </Field>
    </div>
</template>

<script>
import uniqid from 'uniqid';
import HasInputOptions from '../fieldtypes/HasInputOptions.js';
import Converter from '../field-conditions/Converter.js';
import { KEYS, OPERATORS } from '../field-conditions/Constants.js';
import Condition from './Condition.vue';
import { __ } from '../../bootstrap/globals';
import { Field, Input, Button } from '@statamic/ui';
import Select from '@statamic/components/ui/Select/Select.vue';
import Switch from '@statamic/components/ui/Switch.vue';

export default {
    mixins: [HasInputOptions],

    components: {
        Field,
        Input,
        Button,
        Select,
        Switch,
        Condition,
    },

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

            if (this.isStandard && Object.keys(saveableConditions).length) {
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
            let key = KEYS.filter((key) => this.config[key])[0];

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
            conditions = conditions.filter((condition) => {
                return !(!condition.field || !condition.value);
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
