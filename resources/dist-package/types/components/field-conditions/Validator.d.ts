export default class _default {
    constructor(field: any, values: any, rootValues: any, currentFieldPath: any, revealerFields: any, extraPayload: any);
    field: any;
    values: any;
    currentFieldPath: any;
    revealerFields: any;
    extraPayload: any;
    rootValues: any;
    passOnAny: boolean;
    showOnPass: boolean;
    converter: Converter;
    usingRootValues(): this;
    passesConditions(specificConditions: any): any;
    getConditions(): any;
    isCustomConditionWithoutTarget(conditions: any): boolean;
    passesAllConditions(conditions: any): boolean;
    passesAnyConditions(conditions: any): boolean;
    prepareCondition(condition: any): {
        functionName: any;
        params: any;
        target: any;
        targetHandle: any;
    } | {
        lhs: any;
        operator: any;
        rhs: any;
    };
    prepareOperator(operator: any): any;
    prepareLhs(field: any, operator: any): any;
    prepareRhs(rhs: any, operator: any): any;
    prepareCustomCondition(condition: any): {
        functionName: any;
        params: any;
        target: any;
        targetHandle: any;
    };
    prepareFunctionName(condition: any): any;
    prepareParams(condition: any): any;
    getFieldValue(field: any): any;
    passesCondition(condition: any): any;
    passesIncludesCondition(condition: any): any;
    passesIncludesAnyCondition(condition: any): number | boolean;
    passesCustomCondition(condition: any): any;
    passesNonRevealerConditions(dottedPrefix: any): any;
    relativeLhsToAbsoluteFieldPath(lhs: any, dottedPrefix: any): any;
    scopeValuesToParent(): this;
}
import Converter from './Converter.js';
