declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Optional link */
    href: {
        type: StringConstructor;
        default: null;
    };
    /** When `href` is provided, this prop controls the link's `target` attribute */
    target: {
        type: StringConstructor;
        default: null;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        required: true;
    };
    /** Heading text for the empty state item */
    heading: {
        type: StringConstructor;
        required: true;
    };
    /** Optional description text below the heading */
    description: {
        type: StringConstructor;
        default: string;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Optional link */
    href: {
        type: StringConstructor;
        default: null;
    };
    /** When `href` is provided, this prop controls the link's `target` attribute */
    target: {
        type: StringConstructor;
        default: null;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        required: true;
    };
    /** Heading text for the empty state item */
    heading: {
        type: StringConstructor;
        required: true;
    };
    /** Optional description text below the heading */
    description: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{}>, {
    target: string;
    description: string;
    href: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
