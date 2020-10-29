import Converter from './Converter.js';
import { KEYS } from './Constants.js';

const NUMBER_SPECIFIC_COMPARISONS = [
    '>', '>=', '<', '<='
];

export default class {
    constructor(field, values, store, storeName) {
        this.field = field;
        this.values = values;
        this.rootValues = store.state.publish[storeName].values;
        this.store = store;
        this.storeName = storeName;
        this.passOnAny = false;
        this.showOnPass = true;
        this.converter = new Converter;
    }

    passesConditions() {
        let conditions = this.getConditions();

        if (conditions === undefined) {
            return true;
        } else if (_.isString(conditions)) {
            return this.passesCustomCondition(this.prepareCondition(conditions));
        }

        conditions = this.converter.fromBlueprint(conditions, this.field.prefix);

        let passes = this.passOnAny
            ? this.passesAnyConditions(conditions)
            : this.passesAllConditions(conditions);

        return this.showOnPass ? passes : ! passes;
    }

    getConditions() {
        let key = _.chain(KEYS)
            .filter(key => this.field[key])
            .first()
            .value();

        if (! key) {
            return undefined;
        }

        if (key.includes('any')) {
            this.passOnAny = true;
        }

        if (key.includes('unless') || key.includes('hide_when')) {
            this.showOnPass = false;
        }

        return this.field[key];
    }

    passesAllConditions(conditions) {
        return _.chain(conditions)
            .map(condition => this.prepareCondition(condition))
            .reject(condition => this.passesCondition(condition))
            .isEmpty()
            .value();
    }

    passesAnyConditions(conditions) {
        return ! _.chain(conditions)
            .map(condition => this.prepareCondition(condition))
            .filter(condition => this.passesCondition(condition))
            .isEmpty()
            .value();
    }

    prepareCondition(condition) {
        if (_.isString(condition) || condition.operator === 'custom') {
            return this.prepareCustomCondition(condition);
        }

        let operator = this.prepareOperator(condition.operator);
        let lhs = this.prepareLhs(condition.field, operator);
        let rhs = this.prepareRhs(condition.value, operator);

        return {lhs, operator, rhs};
    }

    prepareOperator(operator) {
        switch (operator) {
            case null:
            case '':
            case 'is':
            case 'equals':
                return '==';
            case 'isnt':
            case 'not':
            case '¯\\_(ツ)_/¯':
                return '!=';
            case 'includes':
            case 'contains':
                return 'includes';
            case 'includes_any':
            case 'contains_any':
                return 'includes_any';
        }

        return operator;
    }

    prepareLhs(field, operator) {
        let lhs = this.getFieldValue(field);

        // When performing a number comparison, cast to number.
        if (NUMBER_SPECIFIC_COMPARISONS.includes(operator)) {
            return Number(lhs);
        }

        // When performing lhs.includes(), if lhs is not an object or array, cast to string.
        if (operator === 'includes' && ! _.isObject(lhs)) {
            return lhs ? lhs.toString() : '';
        }

        // When lhs is an empty string, cast to null.
        if (_.isString(lhs) && _.isEmpty(lhs)) {
            lhs = null;
        }

        // Prepare for eval() and return.
        return _.isString(lhs)
            ? JSON.stringify(lhs.trim())
            : lhs;
    }

    prepareRhs(rhs, operator) {
        // When comparing against null, true, false, cast to literals.
        switch (rhs) {
            case 'null':
                return null;
            case 'true':
                return true;
            case 'false':
                return false;
        }

        // When performing a number comparison, cast to number.
        if (NUMBER_SPECIFIC_COMPARISONS.includes(operator)) {
            return Number(rhs);
        }

        // When performing a comparison that cannot be eval()'d, return rhs as is.
        if (rhs === 'empty' || operator === 'includes' || operator === 'includes_any') {
            return rhs;
        }

        // Prepare for eval() and return.
        return _.isString(rhs)
            ? JSON.stringify(rhs.trim())
            : rhs;
    }

    prepareCustomCondition(condition) {
        let functionName = this.prepareFunctionName(condition.value || condition);
        let params = this.prepareParams(condition.value || condition);

        let target = condition.field
            ? this.getFieldValue(condition.field)
            : null;

        return {functionName, params, target};
    }

    prepareFunctionName(condition) {
        return condition
            .replace(new RegExp('^custom .'), '')
            .split(':')[0];
    }

    prepareParams(condition) {
        let params = condition.split(':')[1];

        return params
            ? params.split(',').map(string => string.trim())
            : [];
    }

    getFieldValue(field) {
        return field.startsWith('root.')
            ?  data_get(this.rootValues, field.replace(new RegExp('^root.'), ''))
            :  data_get(this.values, field);
    }

    passesCondition(condition) {
        if (condition.functionName) {
            return this.passesCustomCondition(condition);
        }

        if (condition.operator === 'includes') {
            return this.passesIncludesCondition(condition);
        }

        if (condition.operator === 'includes_any') {
            return this.passesIncludesAnyCondition(condition);
        }

        if (condition.rhs === 'empty') {
            condition.lhs = _.isEmpty(condition.lhs);
            condition.rhs = true;
        }

        return eval(`${condition.lhs} ${condition.operator} ${condition.rhs}`);
    }

    passesIncludesCondition(condition) {
        return condition.lhs.includes(condition.rhs);
    }

    passesIncludesAnyCondition(condition) {
        let values = condition.rhs.split(',').map(string => string.trim());

        if (Array.isArray(condition.lhs)) {
            return _.intersection(condition.lhs, values).length;
        }

        return new RegExp(values.join('|')).test(condition.lhs);
    }

    passesCustomCondition(condition) {
        let customFunction = data_get(this.store.state.statamic.conditions, condition.functionName);

        if (typeof customFunction !== 'function') {
            console.error(`Statamic field condition [${condition.functionName}] was not properly defined.`);
            return false;
        }

        let passes = customFunction({
            params: condition.params,
            target: condition.target,
            values: this.values,
            root: this.rootValues,
            store: this.store,
            storeName: this.storeName,
        });

        return this.showOnPass ? passes : ! passes;
    }
}
