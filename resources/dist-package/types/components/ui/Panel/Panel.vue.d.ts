declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Heading text for the panel */
    heading: {
        type: StringConstructor;
        default: null;
    };
    /** Subheading text below the heading */
    subheading: {
        type: StringConstructor;
        default: null;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Heading text for the panel */
    heading: {
        type: StringConstructor;
        default: null;
    };
    /** Subheading text below the heading */
    subheading: {
        type: StringConstructor;
        default: null;
    };
}>> & Readonly<{}>, {
    heading: string;
    subheading: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    'header-actions'?: ((props: {}) => any) | undefined;
} & {
    default?: ((props: {}) => any) | undefined;
};
