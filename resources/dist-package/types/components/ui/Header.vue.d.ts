declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** The title text displayed in the header. */
    title: {
        type: StringConstructor;
        default: null;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** The title text displayed in the header. */
    title: {
        type: StringConstructor;
        default: null;
    };
}>> & Readonly<{}>, {
    title: string;
    icon: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    title?: ((props: {}) => any) | undefined;
} & {
    actions?: ((props: {}) => any) | undefined;
} & {
    default?: ((props: {}) => any) | undefined;
};
