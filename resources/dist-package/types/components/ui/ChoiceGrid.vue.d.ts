declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The shape of each option's image area, e.g. `16/9`. */
    aspectRatio: {
        type: StringConstructor;
        default: string;
    };
    /** How many options appear per row. */
    columns: {
        type: NumberConstructor;
        default: number;
    };
    /** Whether the choices are disabled. */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the spacing between options. <br><br> Options: `sm`, `base`, `lg` */
    gap: {
        type: StringConstructor;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** Accessible label for the group of choices. */
    label: {
        type: StringConstructor;
        default: null;
    };
    /** The controlled value. An array when `multiple`, otherwise a single value. */
    modelValue: {
        type: (ArrayConstructor | NumberConstructor | StringConstructor)[];
        default: null;
    };
    /** Whether more than one option can be selected. */
    multiple: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Name attribute for the choices. */
    name: {
        type: StringConstructor;
        default: () => string;
    };
    /** The selectable options. Each option supports `value`, `label`, `image` and `badge`. */
    options: {
        type: ArrayConstructor;
        default: () => never[];
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The shape of each option's image area, e.g. `16/9`. */
    aspectRatio: {
        type: StringConstructor;
        default: string;
    };
    /** How many options appear per row. */
    columns: {
        type: NumberConstructor;
        default: number;
    };
    /** Whether the choices are disabled. */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the spacing between options. <br><br> Options: `sm`, `base`, `lg` */
    gap: {
        type: StringConstructor;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** Accessible label for the group of choices. */
    label: {
        type: StringConstructor;
        default: null;
    };
    /** The controlled value. An array when `multiple`, otherwise a single value. */
    modelValue: {
        type: (ArrayConstructor | NumberConstructor | StringConstructor)[];
        default: null;
    };
    /** Whether more than one option can be selected. */
    multiple: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Name attribute for the choices. */
    name: {
        type: StringConstructor;
        default: () => string;
    };
    /** The selectable options. Each option supports `value`, `label`, `image` and `badge`. */
    options: {
        type: ArrayConstructor;
        default: () => never[];
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    name: string;
    options: unknown[];
    label: string;
    disabled: boolean;
    modelValue: string | number | unknown[];
    gap: string;
    multiple: boolean;
    columns: number;
    aspectRatio: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
