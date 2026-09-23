declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Description text for the slider. */
    description: {
        type: StringConstructor;
        default: null;
    };
    /** ID attribute for the slider. */
    id: {
        type: StringConstructor;
        default: () => string;
    };
    /** Label text for the slider. */
    label: {
        type: StringConstructor;
        default: null;
    };
    /** The controlled value of the slider. */
    modelValue: {
        type: NumberConstructor;
        default: number;
    };
    /** The minimum value of the slider. */
    min: {
        type: NumberConstructor;
        default: number;
    };
    /** The maximum value of the slider. */
    max: {
        type: NumberConstructor;
        default: number;
    };
    /** The step increment for the slider. */
    step: {
        type: NumberConstructor;
        default: number;
    };
    /** Controls the size of the slider. <br><br> Options: `sm`, `base` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the slider. <br><br> Options: `default` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Description text for the slider. */
    description: {
        type: StringConstructor;
        default: null;
    };
    /** ID attribute for the slider. */
    id: {
        type: StringConstructor;
        default: () => string;
    };
    /** Label text for the slider. */
    label: {
        type: StringConstructor;
        default: null;
    };
    /** The controlled value of the slider. */
    modelValue: {
        type: NumberConstructor;
        default: number;
    };
    /** The minimum value of the slider. */
    min: {
        type: NumberConstructor;
        default: number;
    };
    /** The maximum value of the slider. */
    max: {
        type: NumberConstructor;
        default: number;
    };
    /** The step increment for the slider. */
    step: {
        type: NumberConstructor;
        default: number;
    };
    /** Controls the size of the slider. <br><br> Options: `sm`, `base` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the slider. <br><br> Options: `default` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    id: string;
    label: string;
    size: string;
    variant: string;
    description: string;
    modelValue: number;
    min: number;
    max: number;
    step: number;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
