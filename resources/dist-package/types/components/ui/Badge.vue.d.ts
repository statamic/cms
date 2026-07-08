declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Appended text */
    append: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** The element or component this component should render as */
    as: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the color of the badge. <br><br> Options: `default`, `amber`, `black`, `blue`, `cyan`, `emerald`, `fuchsia`, `green`, `indigo`, `lime`, `orange`, `pink`, `purple`, `red`, `rose`, `sky`, `teal`, `violet`, `white`, `yellow` */
    color: {
        type: StringConstructor;
        default: string;
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
    /** When `true`, the badge will be displayed as a pill */
    pill: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Prepended text */
    prepend: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** Controls the size of the badge. Options: `sm`, `default`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Text to display in the badge */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Appended text */
    append: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** The element or component this component should render as */
    as: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the color of the badge. <br><br> Options: `default`, `amber`, `black`, `blue`, `cyan`, `emerald`, `fuchsia`, `green`, `indigo`, `lime`, `orange`, `pink`, `purple`, `red`, `rose`, `sky`, `teal`, `violet`, `white`, `yellow` */
    color: {
        type: StringConstructor;
        default: string;
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
    /** When `true`, the badge will be displayed as a pill */
    pill: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Prepended text */
    prepend: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
    /** Controls the size of the badge. Options: `sm`, `default`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Text to display in the badge */
    text: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor | null)[];
        default: null;
    };
}>> & Readonly<{}>, {
    text: string | number | boolean | null;
    target: string;
    size: string;
    append: string | number | boolean | null;
    icon: string;
    as: string;
    color: string;
    href: string;
    iconAppend: string;
    pill: boolean;
    prepend: string | number | boolean | null;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
