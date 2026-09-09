declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The URL to link to */
    href: {
        type: (StringConstructor | null)[];
        default: null;
    };
    /** When `href` is provided, this prop controls the link's `target` attribute */
    target: {
        type: StringConstructor;
        default: null;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: (StringConstructor | null)[];
        default: null;
    };
    /** Controls the heading level */
    level: {
        type: (NumberConstructor | null)[];
        default: null;
    };
    /** Controls the size of the heading. Options: `base`, `lg`, `xl`, `2xl` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** The heading text to display */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The URL to link to */
    href: {
        type: (StringConstructor | null)[];
        default: null;
    };
    /** When `href` is provided, this prop controls the link's `target` attribute */
    target: {
        type: StringConstructor;
        default: null;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: (StringConstructor | null)[];
        default: null;
    };
    /** Controls the heading level */
    level: {
        type: (NumberConstructor | null)[];
        default: null;
    };
    /** Controls the size of the heading. Options: `base`, `lg`, `xl`, `2xl` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** The heading text to display */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
}>> & Readonly<{}>, {
    text: string | number | boolean | null;
    target: string;
    size: string;
    icon: string | null;
    href: string | null;
    level: number | null;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
