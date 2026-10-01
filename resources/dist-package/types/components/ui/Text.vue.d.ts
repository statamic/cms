declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The element this component should render as */
    as: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the size of the text. Options: `xs`, `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Text to display */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** Controls the appearance of the text. Options: `default`, `strong`, `subtle`, `code`, `danger`, `success`, `warning` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The element this component should render as */
    as: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the size of the text. Options: `xs`, `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Text to display */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** Controls the appearance of the text. Options: `default`, `strong`, `subtle`, `code`, `danger`, `success`, `warning` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{}>, {
    text: string | number | boolean | null;
    size: string;
    variant: string;
    as: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
