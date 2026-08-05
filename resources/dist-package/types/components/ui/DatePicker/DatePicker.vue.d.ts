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
    /** The controlled date value. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates) */
    modelValue: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The minimum selectable date. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates) */
    min: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The maximum selectable date. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates) */
    max: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The granularity of the date picker. <br><br> Options: `day`, `hour`, `minute`, `second` */
    granularity: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the calendar is always visible instead of appearing in a popover. */
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The number of months to display in the calendar. */
    numberOfMonths: {
        type: NumberConstructor;
        default: number;
    };
    /** When `true`, a clear button is displayed to reset the date. */
    clearable: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
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
    /** The controlled date value. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates) */
    modelValue: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The minimum selectable date. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates) */
    min: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The maximum selectable date. <br><br> Should be a [`DateValue` object](https://reka-ui.com/docs/guides/dates) */
    max: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The granularity of the date picker. <br><br> Options: `day`, `hour`, `minute`, `second` */
    granularity: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the calendar is always visible instead of appearing in a popover. */
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The number of months to display in the calendar. */
    numberOfMonths: {
        type: NumberConstructor;
        default: number;
    };
    /** When `true`, a clear button is displayed to reset the date. */
    clearable: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    required: boolean;
    disabled: boolean;
    modelValue: string | Record<string, any>;
    min: string | Record<string, any>;
    max: string | Record<string, any>;
    numberOfMonths: number;
    inline: boolean;
    readOnly: boolean;
    clearable: boolean;
    badge: string;
    granularity: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
