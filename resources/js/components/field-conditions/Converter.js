import { OPERATORS, ALIASES } from './Constants.js';

export default class {

    fromBlueprint(conditions, prefix=null) {
        return _.map(conditions, (condition, field) => this.splitRhs(field, condition, prefix));
    }

    toBlueprint(conditions) {
        let converted = {};

        _.each(conditions, condition => {
            converted[condition.field] = this.combineRhs(condition);
        });

        return converted;
    }

    splitRhs(field, condition, prefix=null) {
        return {
            'field': this.getScopedFieldHandle(field, prefix),
            'operator': this.getOperatorFromRhs(condition),
            'value': this.getValueFromRhs(condition)
        };
    }

    getScopedFieldHandle(field, prefix) {
        if (field.startsWith('root.') || ! prefix) {
            return field;
        }

        return prefix + field;
    }

    getOperatorFromRhs(condition) {
        let operator = '==';

        _.chain(this.getOperatorsAndAliases())
            .filter(value => new RegExp(`^${value} [^=]`).test(condition.toString()))
            .each(value => operator = value);

        return this.normalizeOperator(operator);
    }

    normalizeOperator(operator) {
        return ALIASES[operator]
            ? ALIASES[operator]
            : operator;
    }

    getValueFromRhs(condition) {
        let rhs = condition.toString();

        _.chain(this.getOperatorsAndAliases())
            .filter(value => new RegExp(`^${value} [^=]`).test(rhs))
            .each(value => rhs = rhs.replace(new RegExp(`^${value}[ ]*`), ''));

        return rhs;
    }

    combineRhs(condition) {
        let operator = condition.operator ? condition.operator.trim() : '';
        let value = condition.value.trim();

        return `${operator} ${value}`.trim();
    }

    getOperatorsAndAliases() {
        return OPERATORS.concat(Object.keys(ALIASES));
    }
}
