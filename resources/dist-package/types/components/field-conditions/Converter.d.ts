export default class _default {
    fromBlueprint(conditions: any, prefix?: null): {
        field: any;
        operator: any;
        value: any;
    }[];
    toBlueprint(conditions: any): {};
    splitRhs(field: any, condition: any, prefix?: null): {
        field: any;
        operator: any;
        value: any;
    };
    getScopedFieldHandle(field: any, prefix: any): any;
    getOperatorFromRhs(condition: any): any;
    normalizeOperator(operator: any): any;
    getValueFromRhs(condition: any): any;
    combineRhs(condition: any): string;
    getOperatorsAndAliases(): string[];
    normalizeConditionString(value: any): any;
}
