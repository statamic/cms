declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    modelValue: {
        type: ArrayConstructor;
        default: () => never[];
    };
    options: {
        type: ArrayConstructor;
        default: () => never[];
    };
    loadOptions: {
        type: FunctionConstructor;
        default: null;
    };
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
    enableLineBreaks: {
        type: BooleanConstructor;
        default: boolean;
    };
    buttons: {
        type: ArrayConstructor;
        default: () => string[];
    };
    trigger: {
        type: StringConstructor;
        default: string;
    };
    placeholder: {
        type: StringConstructor;
        default: string;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    blur: (...args: any[]) => void;
    focus: (...args: any[]) => void;
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    modelValue: {
        type: ArrayConstructor;
        default: () => never[];
    };
    options: {
        type: ArrayConstructor;
        default: () => never[];
    };
    loadOptions: {
        type: FunctionConstructor;
        default: null;
    };
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
    enableLineBreaks: {
        type: BooleanConstructor;
        default: boolean;
    };
    buttons: {
        type: ArrayConstructor;
        default: () => string[];
    };
    trigger: {
        type: StringConstructor;
        default: string;
    };
    placeholder: {
        type: StringConstructor;
        default: string;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    onBlur?: ((...args: any[]) => any) | undefined;
    onFocus?: ((...args: any[]) => any) | undefined;
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    options: unknown[];
    inline: boolean;
    readOnly: boolean;
    buttons: unknown[];
    modelValue: unknown[];
    loadOptions: Function;
    enableLineBreaks: boolean;
    trigger: string;
    placeholder: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
