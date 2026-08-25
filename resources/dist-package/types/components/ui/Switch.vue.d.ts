declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The id attribute for the toggle */
    id: {
        type: StringConstructor;
        default: () => string;
    };
    /** The controlled value of the switch */
    modelValue: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the switch. <br><br> Options: `xs`, `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Whether the switch is disabled */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The id attribute for the toggle */
    id: {
        type: StringConstructor;
        default: () => string;
    };
    /** The controlled value of the switch */
    modelValue: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the switch. <br><br> Options: `xs`, `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Whether the switch is disabled */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    id: string;
    size: string;
    required: boolean;
    disabled: boolean;
    modelValue: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
