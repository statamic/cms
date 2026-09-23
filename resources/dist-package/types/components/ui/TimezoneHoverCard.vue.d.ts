declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The date to display across timezones. Forwarded to `<Timezones>`. Accepts a `Date`, ISO string, epoch number, or a `{ start, end }` range object. */
    date: {
        type: (ObjectConstructor | DateConstructor | NumberConstructor | StringConstructor)[];
        required: true;
    };
    /** Extra `{ timezone, label }` rows to render above the defaults. Forwarded to `<Timezones>`. */
    additionalTimezones: {
        type: ArrayConstructor;
        default: () => never[];
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The date to display across timezones. Forwarded to `<Timezones>`. Accepts a `Date`, ISO string, epoch number, or a `{ start, end }` range object. */
    date: {
        type: (ObjectConstructor | DateConstructor | NumberConstructor | StringConstructor)[];
        required: true;
    };
    /** Extra `{ timezone, label }` rows to render above the defaults. Forwarded to `<Timezones>`. */
    additionalTimezones: {
        type: ArrayConstructor;
        default: () => never[];
    };
}>> & Readonly<{}>, {
    additionalTimezones: unknown[];
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
