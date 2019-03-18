const KEYS = ['if', 'if_any', 'show_when', 'show_when_any', 'unless', 'unless_any', 'hide_when', 'hide_when_any'];
const OPERATORS = ['==', '!=', '===', '!==', '>', '>=', '<', '<=', 'is', 'equals', 'not', 'includes', 'contains'];
const NUMBER_COMPARISONS = ['>', '>=', '<', '<='];

class FieldConditionsValidator {
    constructor(field, values, store, storeName) {
        this.field = field;
        this.values = values;
        this.rootValues = store.state.publish[storeName].values;
        this.store = store;
        this.storeName = storeName;
        this.passOnAny = false;
        this.showOnPass = true;
    }

    passesConditions() {
        let conditions = this.getConditions();

        if (conditions === undefined) {
            return true;
        } else if (_.isString(conditions)) {
            return this.passesCustomLogicFunction(conditions);
        }

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
            .map((condition, field) => this.normalizeCondition(field, condition))
            .reject(condition => this.passesCondition(condition))
            .isEmpty()
            .value();
    }

    passesAnyConditions(conditions) {
        return ! _.chain(conditions)
            .map((condition, field) => this.normalizeCondition(field, condition))
            .filter(condition => this.passesCondition(condition))
            .isEmpty()
            .value();
    }

    normalizeCondition(field, condition) {
        this.operator = this.normalizeConditionOperator(condition);

        return {
            'lhs': this.normalizeConditionLhs(field),
            'operator': this.operator,
            'rhs': this.normalizeConditionRhs(condition)
        };
    }

    normalizeConditionOperator(condition) {
        let operator = this.getOperatorFromCondition(condition, '==');

        // Normalize operator aliases.
        switch (operator) {
            case 'is':
            case 'equals':
                return '==';
            case 'not':
            case 'isnt':
            case '¯\\_(ツ)_/¯':
                return '!=';
            case 'includes':
            case 'contains':
                return 'includes';
        }

        return operator;
    }

    normalizeConditionLhs(field) {
        let lhs = this.getFieldValue(field);

        // When performing a number comparison, cast to number.
        if (NUMBER_COMPARISONS.includes(this.operator)) {
            return Number(lhs);
        }

        // When performing lhs.includes(), if lhs is not an object or array, cast to string.
        if (this.operator === 'includes' && ! _.isObject(lhs)) {
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

    normalizeConditionRhs(condition) {
        let rhs = this.getRhsFromCondition(condition);

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
        if (NUMBER_COMPARISONS.includes(this.operator)) {
            return Number(rhs);
        }

        // When performing a comparison that cannot be eval()'d, return rhs as is.
        if (rhs === 'empty' || this.operator === 'includes') {
            return rhs;
        }

        // Prepare for eval() and return.
        return _.isString(rhs)
            ? JSON.stringify(rhs.trim())
            : rhs;
    }

    getFieldValue(field) {
        return field.startsWith('root.')
            ?  data_get(this.rootValues, field.replace(new RegExp('^root.'), ''))
            :  data_get(this.values, field);
    }

    getOperatorFromCondition(condition, defaultOperator) {
        let operator = defaultOperator;

        _.chain(OPERATORS)
            .filter(value => new RegExp(`^${value}[^=]`).test(condition.toString()))
            .each(value => operator = value);

        return operator;
    }

    getRhsFromCondition(condition) {
        let rhs = condition.toString();

        _.chain(OPERATORS)
            .filter(value => new RegExp(`^${value}[^=]`).test(rhs))
            .each(value => rhs = rhs.replace(new RegExp(`^${value}[ ]*`), ''));

        return rhs;
    }

    passesCondition(condition) {
        if (condition.operator === 'includes') {
            return condition.lhs.includes(condition.rhs);
        }

        if (condition.rhs === 'empty') {
            condition.lhs = _.isEmpty(condition.lhs);
            condition.rhs = true;
        }

        return eval(`${condition.lhs} ${condition.operator} ${condition.rhs}`);
    }

    passesCustomLogicFunction(functionName) {
        let customFunction = data_get(this.store.state.statamic.conditions, functionName);

        if (typeof customFunction !== 'function') {
            console.error(`Statamic field condition [${functionName}] was not properly defined.`);
            return false;
        }

        let extra = {
            store: this.store,
            storeName: this.storeName
        }

        let passes = customFunction(this.values, this.rootValues, extra);

        return this.showOnPass ? passes : ! passes;
    }
}

// Export select methods for use as Vue mixin.
export default {
    inject: {
        storeName: {
            default: 'base'
        }
    },

    methods: {
        showField(field) {
            let validator = new FieldConditionsValidator(field, this.values, this.$store, this.storeName);

            return validator.passesConditions();
        }
    }
}
