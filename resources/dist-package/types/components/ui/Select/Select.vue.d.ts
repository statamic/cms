declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** When `true`, the dropdown will expand to fit longer option labels. */
    adaptiveWidth: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, the selected value will be clearable. */
    clearable: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** The controlled value of the select. */
    modelValue: {
        type: (ObjectConstructor | NumberConstructor | StringConstructor)[];
        default: null;
    };
    /** Key of the option's label in the option's object. */
    optionLabel: {
        type: StringConstructor;
        default: string;
    };
    /** Array of option objects */
    options: {
        type: ArrayConstructor;
        default: null;
    };
    /** Key of the option's value in the option's object. */
    optionValue: {
        type: StringConstructor;
        default: string;
    };
    placeholder: {
        type: StringConstructor;
        default: () => any;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the select. <br><br> Options: `xs`, `sm`, `base`, `lg`, `xl` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the select. <br><br> Options: `default`, `filled`, `ghost`, `subtle` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:modelValue": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** When `true`, the dropdown will expand to fit longer option labels. */
    adaptiveWidth: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, the selected value will be clearable. */
    clearable: {
        type: BooleanConstructor;
        default: boolean;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** The controlled value of the select. */
    modelValue: {
        type: (ObjectConstructor | NumberConstructor | StringConstructor)[];
        default: null;
    };
    /** Key of the option's label in the option's object. */
    optionLabel: {
        type: StringConstructor;
        default: string;
    };
    /** Array of option objects */
    options: {
        type: ArrayConstructor;
        default: null;
    };
    /** Key of the option's value in the option's object. */
    optionValue: {
        type: StringConstructor;
        default: string;
    };
    placeholder: {
        type: StringConstructor;
        default: () => any;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the size of the select. <br><br> Options: `xs`, `sm`, `base`, `lg`, `xl` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** Controls the appearance of the select. <br><br> Options: `default`, `filled`, `ghost`, `subtle` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
}>, {
    size: string;
    variant: string;
    icon: string;
    disabled: boolean;
    modelValue: string | number | Record<string, any>;
    placeholder: string;
    align: string;
    readOnly: boolean;
    options: unknown[];
    adaptiveWidth: boolean;
    clearable: boolean;
    optionLabel: string;
    optionValue: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    'selected-option'?: ((props: {
        option: any;
    }) => any) | undefined;
} & {
    'no-options'?: ((props: {}) => any) | undefined;
} & {
    option?: ((props: any) => any) | undefined;
};
