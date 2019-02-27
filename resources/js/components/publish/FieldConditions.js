const KEYS = ['if', 'if_any', 'show_when', 'show_when_any', 'unless', 'unless_any', 'hide_when', 'hide_when_any'];
const OPERATORS = ['==', '!=', '===', '!==', '>', '>=', '<', '<=', 'is', 'equals', 'not', 'includes', 'contains'];

class FieldConditionsValidator {
    constructor(field, values, store, storeName) {
        this.field = field;
        this.values = values;
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
        return {
            'lhs': this.normalizeConditionLhs(field),
            'operator': this.normalizeConditionOperator(condition),
            'rhs': this.normalizeConditionRhs(condition)
        };
    }

    normalizeConditionLhs(field) {
        let lhs = data_get(this.values, field, undefined);

        if (_.isString(lhs)) {
            lhs = JSON.stringify(lhs.trim());
        }

        return lhs;
    }

    normalizeConditionOperator(condition) {
        let operator = '==';

        OPERATORS.forEach(value => condition.toString().startsWith(value + ' ') ? operator = value : false);

        this.stringifyRhs = true;

        switch (operator) {
            case 'is':
            case 'equals':
                operator = '==';
                break;
            case 'not':
                operator = '!=';
                break;
            case 'includes':
            case 'contains':
                operator = 'includes';
                this.stringifyRhs = false;
                break;
        }

        return operator;
    }

    normalizeConditionRhs(condition) {
        let rhs = condition;

        OPERATORS.forEach(value => rhs = rhs.toString().replace(new RegExp(`^${value} `), ''));

        switch (rhs) {
            case 'null':
            case 'empty':
                rhs = null;
                break;
            case 'true':
                rhs = true;
                break;
            case 'false':
                rhs = false;
                break;
        }

        if (_.isString(rhs) && this.stringifyRhs) {
            rhs = JSON.stringify(rhs.trim());
        }

        return rhs;
    }

    passesCondition(condition) {
        if (condition.operator === 'includes') {
            return _.isObject(condition.lhs)
                ? condition.lhs.includes(condition.rhs)
                : condition.lhs.toString().includes(condition.rhs);
        }

        return eval(`${condition.lhs} ${condition.operator} ${condition.rhs}`);
    }

    passesCustomLogicFunction(functionName) {
        let customFunction = data_get(Statamic, 'conditions.' + functionName);

        let extra = {
            store: this.store,
            storeName: this.storeName,
            storeValues: this.store.state.publish[this.storeName].values
        }

        let passes = customFunction(this.values, extra);

        return this.showOnPass ? passes : ! passes;
    }
}

// Export select methods for use as Vue mixin.
export default {
    methods: {
        showField(field) {
            let validator = new FieldConditionsValidator(field, this.values, this.$store, this.storeName);

            return validator.passesConditions();
        }
    }
}
