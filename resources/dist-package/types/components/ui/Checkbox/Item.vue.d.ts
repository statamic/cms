declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Controls the vertical alignment of the checkbox with its label. Options: `start`, `center` */
    align: {
        type: StringConstructor;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** Description text to display below the label */
    description: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, disables the checkbox */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, displays the checkbox in an indeterminate state (shows a dash) */
    indeterminate: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Label text to display next to the checkbox */
    label: {
        type: StringConstructor;
        default: null;
    };
    /** The controlled value of the checkbox */
    modelValue: {
        type: (BooleanConstructor | null)[];
        default: null;
    };
    /** Name attribute for the checkbox input */
    name: {
        type: StringConstructor;
        default: null;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the checkbox. Options: `sm`, `base` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, hides the label and description. Use this when the checkbox is used in a context where the label is provided elsewhere, like in a table cell */
    solo: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Value of the checkbox when used in a group */
    value: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor)[];
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    keydown: (...args: any[]) => void;
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Controls the vertical alignment of the checkbox with its label. Options: `start`, `center` */
    align: {
        type: StringConstructor;
        default: string;
        validator: (value: unknown) => boolean;
    };
    /** Description text to display below the label */
    description: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, disables the checkbox */
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, displays the checkbox in an indeterminate state (shows a dash) */
    indeterminate: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Label text to display next to the checkbox */
    label: {
        type: StringConstructor;
        default: null;
    };
    /** The controlled value of the checkbox */
    modelValue: {
        type: (BooleanConstructor | null)[];
        default: null;
    };
    /** Name attribute for the checkbox input */
    name: {
        type: StringConstructor;
        default: null;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the checkbox. Options: `sm`, `base` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, hides the label and description. Use this when the checkbox is used in a context where the label is provided elsewhere, like in a table cell */
    solo: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Value of the checkbox when used in a group */
    value: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor)[];
    };
}>> & Readonly<{
    onKeydown?: ((...args: any[]) => any) | undefined;
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    name: string;
    label: string;
    size: string;
    description: string;
    disabled: boolean;
    modelValue: boolean | null;
    align: string;
    indeterminate: boolean;
    readOnly: boolean;
    solo: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
