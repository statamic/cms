export const injectRadioContext: string | ((context: any) => void);
export const provideRadioContext: string | ((context: any) => void);
declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Controls how the radio group is displayed. Options: `default`, `inline`, `chips` */
    appearance: {
        type: StringConstructor;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** @deprecated Use `appearance="inline"` instead. */
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The controlled value of the radio group */
    modelValue: {
        type: StringConstructor;
        default: null;
    };
    /** Name attribute for the radio group */
    name: {
        type: StringConstructor;
        default: () => string;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {
    focus: () => void;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Controls how the radio group is displayed. Options: `default`, `inline`, `chips` */
    appearance: {
        type: StringConstructor;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** @deprecated Use `appearance="inline"` instead. */
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The controlled value of the radio group */
    modelValue: {
        type: StringConstructor;
        default: null;
    };
    /** Name attribute for the radio group */
    name: {
        type: StringConstructor;
        default: () => string;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    name: string;
    required: boolean;
    modelValue: string;
    inline: boolean;
    appearance: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>, {
    default?: (props: {}) => any;
}>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
