const OPERATORS = ['==', '!=', '===', '!==', '>', '>=', '<', '<=', 'is', 'equals', 'not', 'includes', 'contains'];

class FieldConditionsValidator {
    constructor(field, store) {
        this.field = field;
        this.store = store;
        this.showOnPass = true;
    }

    passesConditions() {
        let conditions = this.getConditions();

        if (conditions === undefined) {
            return true;
        }

        let failedConditions = _.chain(conditions)
            .map((condition, field) => this.normalizeCondition(field, condition))
            .reject(condition => this.passesCondition(condition))
            .value();

        return this.showOnPass ? _.isEmpty(failedConditions) : ! _.isEmpty(failedConditions);
    }

    getConditions() {
        var conditions;

        if (conditions = this.field.if || this.field.show_when) {
            return conditions;
        }

        this.showOnPass = false;

        return this.field.unless || this.field.hide_when;
    }

    normalizeCondition(field, condition) {
        return {
            'lhs': this.normalizeConditionLhs(field),
            'operator': this.normalizeConditionOperator(condition),
            'rhs': this.normalizeConditionRhs(condition)
        };
    }

    normalizeConditionLhs(field) {
        let lhs = data_get(this.store.state.publish.base.values, field, undefined);

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
}

// Export select methods for use as Vue mixin.
export default {
    methods: {
        showField(field) {
            return (new FieldConditionsValidator(field, this.$store)).passesConditions();
        }
    }
}
