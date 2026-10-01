declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    icon: {
        type: StringConstructor;
    };
    title: {
        type: StringConstructor;
        default: () => string;
    };
    blueprint: {
        type: ObjectConstructor;
        required: true;
    };
    initialValues: {
        type: ObjectConstructor;
        required: true;
        default: () => {};
    };
    initialMeta: {
        type: ObjectConstructor;
        required: true;
        default: () => {};
    };
    submitUrl: {
        type: (StringConstructor | null)[];
        default: null;
    };
    submitMethod: {
        type: StringConstructor;
        default: string;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    asConfig: {
        type: BooleanConstructor;
        default: boolean;
    };
    rememberTab: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    icon: {
        type: StringConstructor;
    };
    title: {
        type: StringConstructor;
        default: () => string;
    };
    blueprint: {
        type: ObjectConstructor;
        required: true;
    };
    initialValues: {
        type: ObjectConstructor;
        required: true;
        default: () => {};
    };
    initialMeta: {
        type: ObjectConstructor;
        required: true;
        default: () => {};
    };
    submitUrl: {
        type: (StringConstructor | null)[];
        default: null;
    };
    submitMethod: {
        type: StringConstructor;
        default: string;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    asConfig: {
        type: BooleanConstructor;
        default: boolean;
    };
    rememberTab: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{}>, {
    title: string;
    readOnly: boolean;
    asConfig: boolean;
    rememberTab: boolean;
    initialValues: Record<string, any>;
    initialMeta: Record<string, any>;
    submitUrl: string | null;
    submitMethod: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
