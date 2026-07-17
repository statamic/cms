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
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The URL to link to */
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
        default: null;
    };
    /** Icon name. Will display after the text. [Browse available icons](/?path=/story/components-icon--all-icons) */
    iconAppend: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the button's padding will be adjusted to account for no text */
    iconOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When using `ghost` or `subtle` button variants, you can use the `inset` prop to remove any invisible padding for better alignment */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the button shows an animated loading icon */
    loading: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the button will be rounded */
    round: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the button. Options: `2xs`, `xs`, `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Text to display in the button */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** Unless `href` is provided, this component defaults to a `<button>`. This prop controls the button's `type` attribute */
    type: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the button. Options: `default`, `primary`, `danger`, `filled`, `ghost`, `ghost-pressed`, `subtle`, `pressed` */
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
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The URL to link to */
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
        default: null;
    };
    /** Icon name. Will display after the text. [Browse available icons](/?path=/story/components-icon--all-icons) */
    iconAppend: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the button's padding will be adjusted to account for no text */
    iconOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When using `ghost` or `subtle` button variants, you can use the `inset` prop to remove any invisible padding for better alignment */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the button shows an animated loading icon */
    loading: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the button will be rounded */
    round: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the button. Options: `2xs`, `xs`, `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Text to display in the button */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** Unless `href` is provided, this component defaults to a `<button>`. This prop controls the button's `type` attribute */
    type: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the button. Options: `default`, `primary`, `danger`, `filled`, `ghost`, `ghost-pressed`, `subtle`, `pressed` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{}>, {
    text: string | number | boolean | null;
    target: string;
    size: string;
    type: string;
    variant: string;
    icon: string;
    as: string;
    href: string;
    iconAppend: string;
    disabled: boolean;
    iconOnly: boolean;
    inset: boolean;
    loading: boolean;
    round: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
