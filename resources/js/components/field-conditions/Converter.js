import { KEYS, OPERATORS } from './Constants.js';

export default class {

    fromBlueprint(conditions) {
        return _.map(conditions, (condition, field) => this.splitRhs(field, condition));
    }

    toBlueprint(conditions) {
        let converted = {};

        _.each(conditions, condition => {
            converted[condition.field] = this.combineRhs(condition);
        });

        return converted;
    }

    splitRhs(field, condition) {
        return {
            'field': field,
            'operator': this.getOperatorFromRhs(condition),
            'value': this.getValueFromRhs(condition)
        };
    }

    getOperatorFromRhs(condition) {
        let operator = '==';

        _.chain(OPERATORS)
            .filter(value => new RegExp(`^${value} [^=]`).test(condition.toString()))
            .each(value => operator = value);

        return operator;
    }

    getValueFromRhs(condition) {
        let rhs = condition.toString();

        _.chain(OPERATORS)
            .filter(value => new RegExp(`^${value} [^=]`).test(rhs))
            .each(value => rhs = rhs.replace(new RegExp(`^${value}[ ]*`), ''));

        return rhs;
    }

    combineRhs(condition) {
        let operator = condition.operator ? condition.operator.trim() : '';
        let value = condition.value.trim();

        return `${operator} ${value}`.trim();
    }
}
