declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Whether the rating is disabled. */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** ID attribute for the rating. */
    id: {
        type: StringConstructor;
        default: () => string;
    };
    /** Accessible label for the rating. */
    label: {
        type: StringConstructor;
        default: null;
    };
    /** The number of stars. */
    max: {
        type: NumberConstructor;
        default: number;
    };
    /** The lowest selectable rating. Defaults to a single step. */
    min: {
        type: NumberConstructor;
        default: null;
    };
    /** The controlled value of the rating. */
    modelValue: {
        type: NumberConstructor;
        default: null;
    };
    /** Name attribute for the rating. */
    name: {
        type: StringConstructor;
        default: null;
    };
    /** Controls the size of the stars. <br><br> Options: `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** The increment between ratings. Use `0.5` to allow half stars. */
    step: {
        type: NumberConstructor;
        default: number;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Whether the rating is disabled. */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** ID attribute for the rating. */
    id: {
        type: StringConstructor;
        default: () => string;
    };
    /** Accessible label for the rating. */
    label: {
        type: StringConstructor;
        default: null;
    };
    /** The number of stars. */
    max: {
        type: NumberConstructor;
        default: number;
    };
    /** The lowest selectable rating. Defaults to a single step. */
    min: {
        type: NumberConstructor;
        default: null;
    };
    /** The controlled value of the rating. */
    modelValue: {
        type: NumberConstructor;
        default: null;
    };
    /** Name attribute for the rating. */
    name: {
        type: StringConstructor;
        default: null;
    };
    /** Controls the size of the stars. <br><br> Options: `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** The increment between ratings. Use `0.5` to allow half stars. */
    step: {
        type: NumberConstructor;
        default: number;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    name: string;
    id: string;
    label: string;
    size: string;
    disabled: boolean;
    modelValue: number;
    min: number;
    max: number;
    step: number;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
