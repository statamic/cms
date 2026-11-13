declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The date to display across timezones. Accepts a `Date`, ISO string, epoch number, or a `{ start, end }` range object. */
    date: {
        type: (ObjectConstructor | DateConstructor | NumberConstructor | StringConstructor)[];
        required: true;
    };
    /** Extra `{ timezone, label }` rows to render above the defaults. */
    additionalTimezones: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** When `false`, times are displayed without seconds. */
    showSeconds: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The date to display across timezones. Accepts a `Date`, ISO string, epoch number, or a `{ start, end }` range object. */
    date: {
        type: (ObjectConstructor | DateConstructor | NumberConstructor | StringConstructor)[];
        required: true;
    };
    /** Extra `{ timezone, label }` rows to render above the defaults. */
    additionalTimezones: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** When `false`, times are displayed without seconds. */
    showSeconds: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{}>, {
    additionalTimezones: unknown[];
    showSeconds: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
