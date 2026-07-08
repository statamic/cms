declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    unstyled: {
        type: BooleanConstructor;
        default: boolean;
    };
    contained: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    unstyled: {
        type: BooleanConstructor;
        default: boolean;
    };
    contained: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{}>, {
    unstyled: boolean;
    contained: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    'tbody-start'?: ((props: {}) => any) | undefined;
} & {
    'prepended-row-actions'?: ((props: {
        row: Record<string, any>;
    }) => any) | undefined;
};
