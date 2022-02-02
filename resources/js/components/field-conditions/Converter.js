import { OPERATORS, ALIASES } from './Constants.js';
import map from 'underscore/modules/map.js'
import each from 'underscore/modules/each.js'
import filter from 'underscore/modules/filter.js'
import chain from 'underscore/modules/chain.js'
import chainable from 'underscore/modules/mixin.js'

chainable({ chain, filter, each });

export default class {

    fromBlueprint(conditions, prefix=null) {
        return map(conditions, (condition, field) => this.splitRhs(field, condition, prefix));
    }

    toBlueprint(conditions) {
        let converted = {};

        each(conditions, condition => {
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

        chain(this.getOperatorsAndAliases())
            .filter(value => new RegExp(`^${value} [^=]`).test(this.normalizeConditionString(condition)))
            .each(value => operator = value);

        return this.normalizeOperator(operator);
    }

    normalizeOperator(operator) {
        return ALIASES[operator]
            ? ALIASES[operator]
            : operator;
    }

    getValueFromRhs(condition) {
        let rhs = this.normalizeConditionString(condition);

        chain(this.getOperatorsAndAliases())
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

    normalizeConditionString(value) {
        // You cannot `null.toString()`, so we'll manually cast it here to prevent error.
        if (value === null) return 'null';

        // Note: We don't document the use of an '' empty string in the yaml,
        // but for the people that manually add this to their yaml, we'll
        // treat it as an `empty` check so that it doesn't feel broken.
        if (value === '') return 'empty';

        return value.toString();
    }
}
