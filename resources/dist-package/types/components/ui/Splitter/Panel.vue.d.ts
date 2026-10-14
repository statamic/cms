declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** When `true`, allows the panel to be collapsed */
    collapsible: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Size of the panel when collapsed (percentage) */
    collapsedSize: {
        type: NumberConstructor;
        default: number;
    };
    /** Minimum size of the panel (percentage) */
    minSize: {
        type: NumberConstructor;
        default: number;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** When `true`, allows the panel to be collapsed */
    collapsible: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Size of the panel when collapsed (percentage) */
    collapsedSize: {
        type: NumberConstructor;
        default: number;
    };
    /** Minimum size of the panel (percentage) */
    minSize: {
        type: NumberConstructor;
        default: number;
    };
}>> & Readonly<{}>, {
    collapsible: boolean;
    collapsedSize: number;
    minSize: number;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
