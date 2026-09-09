declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    action: {
        type: ObjectConstructor;
        required: true;
    };
    selections: {
        type: NumberConstructor;
        required: true;
    };
    errors: {
        type: ObjectConstructor;
    };
    isDirty: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {
    handle: any;
    confirm: typeof confirm;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    confirmed: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    action: {
        type: ObjectConstructor;
        required: true;
    };
    selections: {
        type: NumberConstructor;
        required: true;
    };
    errors: {
        type: ObjectConstructor;
    };
    isDirty: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    onConfirmed?: ((...args: any[]) => any) | undefined;
}>, {
    isDirty: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
declare function confirm(): void;
