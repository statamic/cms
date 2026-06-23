declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The element or component this component should render as */
    as: {
        type: StringConstructor;
        default: null;
    };
    /** The URL to link to */
    href: {
        type: StringConstructor;
        default: null;
    };
    /** When `href` is provided, this prop controls the link's `target` attribute */
    target: {
        type: StringConstructor;
        default: string;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** Text to display in the item */
    text: {
        type: StringConstructor;
        default: null;
    };
    /** Controls the appearance of the context item. Options: `default`, `destructive` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The element or component this component should render as */
    as: {
        type: StringConstructor;
        default: null;
    };
    /** The URL to link to */
    href: {
        type: StringConstructor;
        default: null;
    };
    /** When `href` is provided, this prop controls the link's `target` attribute */
    target: {
        type: StringConstructor;
        default: string;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** Text to display in the item */
    text: {
        type: StringConstructor;
        default: null;
    };
    /** Controls the appearance of the context item. Options: `default`, `destructive` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{}>, {
    text: string;
    target: string;
    variant: string;
    icon: string;
    as: string;
    href: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
