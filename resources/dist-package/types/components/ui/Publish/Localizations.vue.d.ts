declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    localizations: {
        type: ArrayConstructor;
        required: true;
    };
    localizing: {
        type: (StringConstructor | BooleanConstructor)[];
        default: boolean;
    };
    heading: {
        type: StringConstructor;
        default: null;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    selected: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    localizations: {
        type: ArrayConstructor;
        required: true;
    };
    localizing: {
        type: (StringConstructor | BooleanConstructor)[];
        default: boolean;
    };
    heading: {
        type: StringConstructor;
        default: null;
    };
}>> & Readonly<{
    onSelected?: ((...args: any[]) => any) | undefined;
}>, {
    heading: string;
    localizing: string | boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
