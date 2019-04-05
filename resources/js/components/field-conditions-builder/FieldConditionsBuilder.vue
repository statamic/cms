<template>

    <div class="form-group publish-field select-fieldtype field-w-full">
        <label class="publish-field-label">{{ __('Conditions') }}</label>
        <div class="help-block -mt-1"><p>{{ __('When to show or hide this field.') }}</p></div>

        <select-input
            v-model="when"
            :options="whenOptions"
            :placeholder="false"
            class="inline-block" />

        <select-input
            v-if="showConditions"
            v-model="type"
            :options="typeOptions"
            :placeholder="false"
            class="inline-block ml-2" />

        <text-input
            v-if="showConditions && isCustom"
            v-model="customMethod"
            class="w-1/2 mt-2" />

        <div
            v-if="showConditions && isStandard"
            v-for="(condition, index) in conditions"
            :key="condition.field"
            class="mt-3"
        >
            <select-input
                v-model="conditions[index].field"
                :options="fieldOptions"
                :placeholder="false"
                class="inline-block" />

            <select-input
                v-model="conditions[index].operator"
                :options="operatorOptions"
                :placeholder="false"
                class="inline-block ml-2" />

            <text-input
                v-model="conditions[index].value"
                class="w-1/2 mt-2" />

            <button
                v-if="canRemove"
                @click="remove(index)"
                v-text="__('Delete')" />
        </div>

        <button
            v-if="showConditions && isStandard"
            v-text="__('Add Condition')"
            @click="add"
            class="btn mt-3" />

    </div>

</template>


<script>
import HasInputOptions from '../fieldtypes/HasInputOptions.js'
import { FieldConditions, KEYS, OPERATORS } from '../publish/FieldConditions.js'

export default {

    mixins: [HasInputOptions],

    components: {
        condition: require('./Condition.vue')
    },

    props: {
        config: {
            required: true
        }
    },

    data() {
        return {
            when: 'always',
            type: 'all',
            customMethod: null,
            conditions: [],
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
                all: __('All of following conditions pass'),
                any: __('Any of following conditions pass'),
                custom: __('Custom method passes')
            });
        },

        fieldOptions() {
            return this.normalizeInputOptions(['one', 'two']);
        },

        operatorOptions() {
            return this.normalizeInputOptions(
                _.reject(OPERATORS, operator => ['is', 'isnt', '==', '!='].includes(operator))
            );
        },

        showConditions() {
            return this.when !== 'always';
        },

        isStandard() {
            return ! this.isCustom;
        },

        isCustom() {
            return this.type === 'custom';
        },

        canRemove() {
            return this.conditions.length > 1;
        },

        saveableConditions() {
            var conditions = {};
            let key = this.type === 'any' ? `${this.when}_any` : this.when;

            if (this.isStandard && this.conditions.length > 0) {
                conditions[key] = this.combineOperatorsAndValues(this.conditions);
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
        }
    },

    created() {
        // this.add();
        this.getInitial();
    },

    methods: {
        add() {
            this.conditions.push({
                field: null,
                operator: 'equals',
                value: null
            });
        },

        remove(index) {
            this.conditions.splice(index, 1);
        },

        getInitial() {
            let key = _.chain(KEYS)
                .filter(key => this.config[key])
                .first()
                .value();

            let conditions = this.config[key];

            if (! conditions) {
                return;
            }

            this.when = key.startsWith('unless') || key.startsWith('hide_when') ? 'unless' : 'if';
            this.type = key.endsWith('_any') ? 'any' : 'all';

            if (typeof conditions === 'string') {
                this.type = 'custom';
                this.customMethod = conditions;
            } else {
                this.conditions = this.splitOperatorsAndValues(conditions);
            }
        },

        splitOperatorsAndValues(conditions) {
            return _.map(conditions, (rhs, field) => {
                return {
                    'field': field,
                    'operator': this.editableOperator(rhs),
                    'value': this.editableValue(rhs)
                }
            });
        },

        combineOperatorsAndValues(conditions) {
            let combined = {};

            return conditions.forEach(condition => {
                let operator = this.saveableOperator(condition.operator);
                let value = this.saveableValue(condition.value);
                combined[condition.field] = `${operator} ${value}`.trim();
            });

            return combined;
        },

        editableOperator(operator) {
            return this.normalizeOperator(operator);
        },

        saveableOperator(operator) {
            operator = this.normalizeOperator(operator);

            if (operator === 'equals') {
                operator = '';
            }

            return operator;
        },

        editableValue(value) {
            let fieldConditions = new FieldConditions();
            let wat = fieldConditions.normalizeConditionRhs(value);
            console.log(wat);
            return wat;
        },

        saveableValue(value) {
            return value;
        },

        normalizeOperator(operator) {
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
    }
}
</script>
