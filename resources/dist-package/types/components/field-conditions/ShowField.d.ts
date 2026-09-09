export default class _default {
    constructor(values: any, extraValues: any, rootValues: any, revealerValues: any, hiddenFields: any, setHiddenField: any, extraPayload: any);
    values: any;
    extraValues: any;
    rootValues: any;
    revealerValues: any;
    hiddenFields: any;
    setHiddenField: any;
    extraPayload: any;
    showField(field: any, dottedKey: any): any;
    setHiddenFieldState({ dottedKey, hidden, omitValue }: {
        dottedKey: any;
        hidden: any;
        omitValue: any;
    }): void;
    shouldForceHiddenField(dottedFieldPath: any): boolean;
}
