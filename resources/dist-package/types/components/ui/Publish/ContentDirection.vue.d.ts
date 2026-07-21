declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The element or component to render as */
    as: {
        type: (ObjectConstructor | StringConstructor)[];
        default: string;
    };
    /** When `true`, merges props onto the immediate child instead of rendering a wrapper element */
    asChild: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The element or component to render as */
    as: {
        type: (ObjectConstructor | StringConstructor)[];
        default: string;
    };
    /** When `true`, merges props onto the immediate child instead of rendering a wrapper element */
    asChild: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{}>, {
    as: string | Record<string, any>;
    asChild: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
