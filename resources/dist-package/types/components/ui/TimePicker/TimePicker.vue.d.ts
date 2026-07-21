declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Badge text to display. */
    badge: {
        type: StringConstructor;
        default: null;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The controlled time value. */
    modelValue: {
        type: ObjectConstructor;
        default: null;
    };
    /** The granularity of the time picker. <br><br> Options: `hour`, `minute`, `second` */
    granularity: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, clear and "set to now" buttons are displayed. */
    clearable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the time picker is disabled. */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the time picker is read-only. */
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Badge text to display. */
    badge: {
        type: StringConstructor;
        default: null;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The controlled time value. */
    modelValue: {
        type: ObjectConstructor;
        default: null;
    };
    /** The granularity of the time picker. <br><br> Options: `hour`, `minute`, `second` */
    granularity: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, clear and "set to now" buttons are displayed. */
    clearable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the time picker is disabled. */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the time picker is read-only. */
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    required: boolean;
    disabled: boolean;
    modelValue: Record<string, any>;
    readOnly: boolean;
    clearable: boolean;
    badge: string;
    granularity: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
