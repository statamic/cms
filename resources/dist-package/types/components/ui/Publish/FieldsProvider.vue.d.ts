export const injectFieldsContext: string | ((context: any) => void);
export const provideFieldsContext: string | ((context: any) => void);
declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    fields: {
        type: ArrayConstructor;
        required: true;
    };
    fieldPathPrefix: {
        type: StringConstructor;
    };
    metaPathPrefix: {
        type: StringConstructor;
        default: (props: any) => any;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    asConfig: {
        type: BooleanConstructor;
        default: undefined;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    fields: {
        type: ArrayConstructor;
        required: true;
    };
    fieldPathPrefix: {
        type: StringConstructor;
    };
    metaPathPrefix: {
        type: StringConstructor;
        default: (props: any) => any;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    asConfig: {
        type: BooleanConstructor;
        default: undefined;
    };
}>> & Readonly<{}>, {
    readOnly: boolean;
    metaPathPrefix: string;
    asConfig: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>, {
    default?: (props: {}) => any;
}>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
