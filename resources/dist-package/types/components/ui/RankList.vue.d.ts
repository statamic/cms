declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Whether the list can be reordered. */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The controlled order, as an array of option values. */
    modelValue: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** Name attribute for the hidden inputs holding the order. */
    name: {
        type: StringConstructor;
        default: null;
    };
    /** The items to rank. Each option supports `value` and `label`. */
    options: {
        type: ArrayConstructor;
        default: () => never[];
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Whether the list can be reordered. */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The controlled order, as an array of option values. */
    modelValue: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** Name attribute for the hidden inputs holding the order. */
    name: {
        type: StringConstructor;
        default: null;
    };
    /** The items to rank. Each option supports `value` and `label`. */
    options: {
        type: ArrayConstructor;
        default: () => never[];
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    name: string;
    options: unknown[];
    disabled: boolean;
    modelValue: unknown[];
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
