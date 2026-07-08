declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The controlled value of the calendar. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`) */
    modelValue: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The earliest date that can be selected. Dates before this will be disabled. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`) */
    min: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The latest date that can be selected. Dates after this will be disabled. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`) */
    max: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** If necessary, you can you swap out any of the internal Calendar components by passing an object to this prop. */
    components: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** The number of months to display at once. */
    numberOfMonths: {
        type: NumberConstructor;
        default: number;
    };
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The controlled value of the calendar. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`) */
    modelValue: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The earliest date that can be selected. Dates before this will be disabled. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`) */
    min: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** The latest date that can be selected. Dates after this will be disabled. <br><br> Should be an ISO 8601 date and time string with a UTC offset (eg. `2021-11-07T07:45:00Z` or `2021-11-07T07:45:00-07:00`) */
    max: {
        type: (ObjectConstructor | StringConstructor)[];
        default: null;
    };
    /** If necessary, you can you swap out any of the internal Calendar components by passing an object to this prop. */
    components: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** The number of months to display at once. */
    numberOfMonths: {
        type: NumberConstructor;
        default: number;
    };
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    modelValue: string | Record<string, any>;
    min: string | Record<string, any>;
    max: string | Record<string, any>;
    components: Record<string, any>;
    numberOfMonths: number;
    inline: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
