declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** When `true`, the internal padding of the card is removed. */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the appearance of the card. <br><br> Options: `default`, `flat` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** When `true`, the internal padding of the card is removed. */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the appearance of the card. <br><br> Options: `default`, `flat` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{}>, {
    variant: string;
    inset: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
