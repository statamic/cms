declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    display: {
        type: StringConstructor;
        required: true;
    };
    fields: {
        type: ArrayConstructor;
        required: true;
    };
    meta: {
        type: ObjectConstructor;
        required: true;
    };
    values: {
        type: ObjectConstructor;
        required: true;
    };
}>, {
    focusFirstField: () => void;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:values": (...args: any[]) => void;
    removed: (...args: any[]) => void;
    "enter-pressed": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    display: {
        type: StringConstructor;
        required: true;
    };
    fields: {
        type: ArrayConstructor;
        required: true;
    };
    meta: {
        type: ObjectConstructor;
        required: true;
    };
    values: {
        type: ObjectConstructor;
        required: true;
    };
}>> & Readonly<{
    "onUpdate:values"?: ((...args: any[]) => any) | undefined;
    onRemoved?: ((...args: any[]) => any) | undefined;
    "onEnter-pressed"?: ((...args: any[]) => any) | undefined;
}>, {}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
