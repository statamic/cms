declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** When `true`, the textarea will automatically grow/shrink to fit content */
    elastic: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows a copy button to copy the value to clipboard */
    copyable: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** ID attribute for the textarea element */
    id: {
        type: StringConstructor;
        default: null;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls resize behavior. Options: `both`, `horizontal`, `vertical`, `none` */
    resize: {
        type: StringConstructor;
        default: string;
    };
    /** Number of visible text rows */
    rows: {
        type: (NumberConstructor | StringConstructor)[];
        default: number;
    };
    /** The controlled value of the textarea */
    modelValue: {
        type: StringConstructor;
        default: null;
    };
    /** Specify a character limit */
    limit: {
        type: NumberConstructor;
        default: null;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** When `true`, the textarea will automatically grow/shrink to fit content */
    elastic: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows a copy button to copy the value to clipboard */
    copyable: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** ID attribute for the textarea element */
    id: {
        type: StringConstructor;
        default: null;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls resize behavior. Options: `both`, `horizontal`, `vertical`, `none` */
    resize: {
        type: StringConstructor;
        default: string;
    };
    /** Number of visible text rows */
    rows: {
        type: (NumberConstructor | StringConstructor)[];
        default: number;
    };
    /** The controlled value of the textarea */
    modelValue: {
        type: StringConstructor;
        default: null;
    };
    /** Specify a character limit */
    limit: {
        type: NumberConstructor;
        default: null;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    id: string;
    resize: string;
    required: boolean;
    disabled: boolean;
    modelValue: string;
    limit: number;
    readOnly: boolean;
    rows: string | number;
    copyable: boolean;
    elastic: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
