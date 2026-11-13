declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The controlled value of the editable text */
    modelValue: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the input will be automatically focused when the component mounts */
    startWithEditMode: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls when the edit is submitted. <br><br> Options: `blur`, `none`, `enter`, `both` */
    submitMode: {
        type: StringConstructor;
        default: string;
    };
    placeholder: {
        type: StringConstructor;
        default: () => any;
    };
}>, {
    edit: typeof edit;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    cancel: (...args: any[]) => void;
    submit: (...args: any[]) => void;
    "update:modelValue": (...args: any[]) => void;
    edit: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The controlled value of the editable text */
    modelValue: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the input will be automatically focused when the component mounts */
    startWithEditMode: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls when the edit is submitted. <br><br> Options: `blur`, `none`, `enter`, `both` */
    submitMode: {
        type: StringConstructor;
        default: string;
    };
    placeholder: {
        type: StringConstructor;
        default: () => any;
    };
}>> & Readonly<{
    onCancel?: ((...args: any[]) => any) | undefined;
    onSubmit?: ((...args: any[]) => any) | undefined;
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
    onEdit?: ((...args: any[]) => any) | undefined;
}>, {
    modelValue: string;
    placeholder: string;
    startWithEditMode: boolean;
    submitMode: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
declare function edit(): void;
