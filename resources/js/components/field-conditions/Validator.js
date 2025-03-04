import Converter from './Converter.js';
import ParentResolver from './ParentResolver.js';
import { KEYS } from './Constants.js';
import { data_get } from '../../bootstrap/globals.js';
import { isString, isObject, isEmpty, intersection } from 'lodash-es';

const NUMBER_SPECIFIC_COMPARISONS = ['>', '>=', '<', '<='];

export default class {
    constructor(field, values, dottedFieldPath, store) {
        this.field = field;
        this.values = values;
        this.dottedFieldPath = dottedFieldPath;
        this.store = store;
        this.rootValues = store ? store.values : false;
        this.passOnAny = false;
        this.showOnPass = true;
        this.converter = new Converter();
    }

    passesConditions(specificConditions) {
        let conditions = specificConditions || this.getConditions();

        if (conditions === undefined) {
            return true;
        } else if (this.isCustomConditionWithoutTarget(conditions)) {
            return this.passesCustomCondition(this.prepareCondition(conditions));
        }

        let passes = this.passOnAny ? this.passesAnyConditions(conditions) : this.passesAllConditions(conditions);

        return this.showOnPass ? passes : !passes;
    }

    getConditions() {
        let key = KEYS.filter((key) => this.field[key])[0];

        if (!key) {
            return undefined;
        }

        if (key.includes('any')) {
            this.passOnAny = true;
        }

        if (key.includes('unless') || key.includes('hide_when')) {
            this.showOnPass = false;
        }

        let conditions = this.field[key];

        return this.isCustomConditionWithoutTarget(conditions)
            ? conditions
            : this.converter.fromBlueprint(conditions, this.field.prefix);
    }

    isCustomConditionWithoutTarget(conditions) {
        return isString(conditions);
    }

    passesAllConditions(conditions) {
        return isEmpty(
            conditions
                .map((condition) => this.prepareCondition(condition))
                .filter((condition) => !this.passesCondition(condition)),
        );
    }

    passesAnyConditions(conditions) {
        return !isEmpty(
            conditions
                .map((condition) => this.prepareCondition(condition))
                .filter((condition) => this.passesCondition(condition)),
        );
    }

    prepareCondition(condition) {
        if (isString(condition) || condition.operator === 'custom') {
            return this.prepareCustomCondition(condition);
        }

        let operator = this.prepareOperator(condition.operator);
        let lhs = this.prepareLhs(condition.field, operator);
        let rhs = this.prepareRhs(condition.value, operator);

        return { lhs, operator, rhs };
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
        if (operator === 'includes' && !isObject(lhs)) {
            return lhs ? lhs.toString() : '';
        }

        // When lhs is an empty string, cast to null.
        if (isString(lhs) && isEmpty(lhs)) {
            lhs = null;
        }

        // Prepare for eval() and return.
        return isString(lhs) ? JSON.stringify(lhs.trim()) : lhs;
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
        return isString(rhs) ? JSON.stringify(rhs.trim()) : rhs;
    }

    prepareCustomCondition(condition) {
        let functionName = this.prepareFunctionName(condition.value || condition);
        let params = this.prepareParams(condition.value || condition);

        let target = condition.field ? this.getFieldValue(condition.field) : null;
        let targetHandle = condition.field;

        return { functionName, params, target, targetHandle };
    }

    prepareFunctionName(condition) {
        return condition.replace(new RegExp('^custom '), '').split(':')[0];
    }

    prepareParams(condition) {
        let params = condition.split(':')[1];

        return params ? params.split(',').map((string) => string.trim()) : [];
    }

    getFieldValue(field) {
        if (field.startsWith('$parent.')) {
            field = new ParentResolver(this.dottedFieldPath).resolve(field);
        }

        if (field.startsWith('$root.') || field.startsWith('root.')) {
            return data_get(this.rootValues, field.replace(new RegExp('^\\$?root\\.'), ''));
        }

        return data_get(this.values, field);
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
            condition.lhs = isEmpty(condition.lhs);
            condition.rhs = true;
        }

        if (isObject(condition.lhs)) {
            return false;
        }

        return eval(`${condition.lhs} ${condition.operator} ${condition.rhs}`);
    }

    passesIncludesCondition(condition) {
        return condition.lhs.includes(condition.rhs);
    }

    passesIncludesAnyCondition(condition) {
        let values = condition.rhs.split(',').map((string) => string.trim());

        if (Array.isArray(condition.lhs)) {
            return intersection(condition.lhs, values).length;
        }

        return new RegExp(values.join('|')).test(condition.lhs);
    }

    passesCustomCondition(condition) {
        let customFunction = Statamic.$conditions.get(condition.functionName);

        if (typeof customFunction !== 'function') {
            console.error(`Statamic field condition [${condition.functionName}] was not properly defined.`);
            return false;
        }

        let passes = customFunction({
            params: condition.params,
            target: condition.target,
            targetHandle: condition.targetHandle,
            values: this.values,
            root: this.rootValues,
            store: this.store,
            fieldPath: this.dottedFieldPath,
        });

        return this.showOnPass ? passes : !passes;
    }

    passesNonRevealerConditions(dottedPrefix) {
        let conditions = this.getConditions();

        if (this.isCustomConditionWithoutTarget(conditions)) {
            return this.passesConditions(conditions);
        }

        let revealerFields = this.store.revealerFields || [];

        let nonRevealerConditions = (this.getConditions() ?? []).filter(
            (condition) => !revealerFields.includes(this.relativeLhsToAbsoluteFieldPath(condition.field, dottedPrefix)),
        );

        return this.passesConditions(nonRevealerConditions);
    }

    relativeLhsToAbsoluteFieldPath(lhs, dottedPrefix) {
        if (lhs.startsWith('$parent.')) {
            lhs = new ParentResolver(this.dottedFieldPath).resolve(lhs);
        }

        if (lhs.startsWith('$root.') || lhs.startsWith('root.')) {
            return lhs.replace(new RegExp('^\\$?root\\.'), '');
        }

        return dottedPrefix ? dottedPrefix + '.' + lhs : lhs;
    }
}
