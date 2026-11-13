declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Controls the size of the subheading. Options: `sm`, `base`, `lg`, `xl` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Text to display in the subheading */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Controls the size of the subheading. Options: `sm`, `base`, `lg`, `xl` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Text to display in the subheading */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
}>> & Readonly<{}>, {
    text: string | number | boolean | null;
    size: string;
    icon: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
