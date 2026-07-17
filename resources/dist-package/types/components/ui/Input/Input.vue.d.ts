declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Appended text */
    append: {
        type: StringConstructor;
        default: null;
    };
    /** Badge text to display on the right side */
    badge: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, shows a clear button to empty the input */
    clearable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows a copy button to copy the value to clipboard */
    copyable: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
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
    /** Icon name. Will display before the text. [Browse available icons](/?path=/story/components-icon--all-icons) */
    iconPrepend: {
        type: StringConstructor;
        default: null;
    };
    /** ID attribute for the input element */
    id: {
        type: StringConstructor;
        default: () => string;
    };
    /** Specify a character limit */
    limit: {
        type: NumberConstructor;
        default: null;
    };
    /** When `true`, an animated loading indicator will show next to the input */
    loading: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The controlled value of the input */
    modelValue: {
        type: (NumberConstructor | StringConstructor)[];
        default: null;
    };
    placeholder: {
        type: StringConstructor;
        default: null;
    };
    /** Prepended text */
    prepend: {
        type: StringConstructor;
        default: null;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the input. Options: `xs`, `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Input type attribute */
    type: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the input. Options: `default`, `filled` */
    variant: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, shows an eye icon to toggle password visibility */
    viewable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, autofocuses the input on mount */
    focus: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Additional attributes to apply to the input element */
    inputAttrs: {
        type: (ObjectConstructor | StringConstructor)[];
        default: () => {};
    };
    /** Additional CSS classes for the input element */
    inputClass: {
        type: StringConstructor;
        default: string;
    };
}>, {
    focus: () => void;
    select: () => void;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Appended text */
    append: {
        type: StringConstructor;
        default: null;
    };
    /** Badge text to display on the right side */
    badge: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, shows a clear button to empty the input */
    clearable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows a copy button to copy the value to clipboard */
    copyable: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
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
    /** Icon name. Will display before the text. [Browse available icons](/?path=/story/components-icon--all-icons) */
    iconPrepend: {
        type: StringConstructor;
        default: null;
    };
    /** ID attribute for the input element */
    id: {
        type: StringConstructor;
        default: () => string;
    };
    /** Specify a character limit */
    limit: {
        type: NumberConstructor;
        default: null;
    };
    /** When `true`, an animated loading indicator will show next to the input */
    loading: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The controlled value of the input */
    modelValue: {
        type: (NumberConstructor | StringConstructor)[];
        default: null;
    };
    placeholder: {
        type: StringConstructor;
        default: null;
    };
    /** Prepended text */
    prepend: {
        type: StringConstructor;
        default: null;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the input. Options: `xs`, `sm`, `base`, `lg` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Input type attribute */
    type: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the input. Options: `default`, `filled` */
    variant: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, shows an eye icon to toggle password visibility */
    viewable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, autofocuses the input on mount */
    focus: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Additional attributes to apply to the input element */
    inputAttrs: {
        type: (ObjectConstructor | StringConstructor)[];
        default: () => {};
    };
    /** Additional CSS classes for the input element */
    inputClass: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    id: string;
    focus: boolean;
    size: string;
    append: string;
    type: string;
    variant: string;
    icon: string;
    required: boolean;
    iconAppend: string;
    prepend: string;
    disabled: boolean;
    loading: boolean;
    iconPrepend: string;
    modelValue: string | number;
    placeholder: string;
    limit: number;
    readOnly: boolean;
    clearable: boolean;
    badge: string;
    copyable: boolean;
    viewable: boolean;
    inputAttrs: string | Record<string, any>;
    inputClass: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    prepend?: ((props: {}) => any) | undefined;
} & {
    append?: ((props: {}) => any) | undefined;
};
