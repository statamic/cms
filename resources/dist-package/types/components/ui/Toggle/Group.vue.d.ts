declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The controlled value of the toggle group. */
    modelValue: {
        type: (ArrayConstructor | StringConstructor)[];
        default: null;
    };
    /** Controls the size of the toggle items. <br><br> Options: `xs`, `sm`, `base` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the toggle items. <br><br> Options: `default`, `primary`, `filled`, `ghost` */
    variant: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, multiple items can be selected */
    multiple: {
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
    /** The controlled value of the toggle group. */
    modelValue: {
        type: (ArrayConstructor | StringConstructor)[];
        default: null;
    };
    /** Controls the size of the toggle items. <br><br> Options: `xs`, `sm`, `base` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the toggle items. <br><br> Options: `default`, `primary`, `filled`, `ghost` */
    variant: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, multiple items can be selected */
    multiple: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    size: string;
    variant: string;
    required: boolean;
    modelValue: string | unknown[];
    multiple: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
