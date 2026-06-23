declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** When `true`, the dropdown will expand to fit longer option labels. Not recommended for large datasets. */
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
    /** When `true`, the options dropdown will close after selecting an option. */
    closeOnSelect: {
        type: BooleanConstructor;
        default: undefined;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the focus outline will be more discrete. */
    discreteFocusOutline: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** ID attribute for the input element */
    id: {
        type: StringConstructor;
    };
    /** When `true`, the Combobox will avoid filtering options, allowing you to handle filtering yourself by listening to the `search` event and updating the `options` prop. */
    ignoreFilter: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the option labels will be rendered with `v-html` instead of `v-text`. */
    labelHtml: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The maximum number of selectable options. */
    maxSelections: {
        type: NumberConstructor;
        default: null;
    };
    /** The controlled value of the combobox. */
    modelValue: {
        type: (ObjectConstructor | NumberConstructor | StringConstructor)[];
        default: null;
    };
    /** When `true`, multiple options are allowed. */
    multiple: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Key of the option's label in the option's object. */
    optionLabel: {
        type: StringConstructor;
        default: string;
    };
    /** Array of option objects */
    options: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** Key of the option's value in the option's object. */
    optionValue: {
        type: StringConstructor;
        default: string;
    };
    /** The delimiter used to split pasted text into multiple tags. Only used when `taggable` is `true`. */
    pasteDelimiter: {
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
    /** When `true`, the options will be searchable. */
    searchable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Determines if the dropdown should open */
    shouldOpenDropdown: {
        type: FunctionConstructor;
        default: () => boolean;
    };
    /** Controls the size of the combobox. <br><br> Options: `xs`, `sm`, `base`, `lg`, `xl` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, additional options can be added by typing in the search input and pressing enter. */
    taggable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the appearance of the combobox. <br><br> Options: `default`, `filled`, `ghost`, `subtle` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>, {
    searchQuery: import("vue").Ref<string, string>;
    filteredOptions: import("vue").ComputedRef<unknown[]>;
    focus: typeof focus;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    search: (...args: any[]) => void;
    "update:modelValue": (...args: any[]) => void;
    selected: (...args: any[]) => void;
    added: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** When `true`, the dropdown will expand to fit longer option labels. Not recommended for large datasets. */
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
    /** When `true`, the options dropdown will close after selecting an option. */
    closeOnSelect: {
        type: BooleanConstructor;
        default: undefined;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the focus outline will be more discrete. */
    discreteFocusOutline: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** ID attribute for the input element */
    id: {
        type: StringConstructor;
    };
    /** When `true`, the Combobox will avoid filtering options, allowing you to handle filtering yourself by listening to the `search` event and updating the `options` prop. */
    ignoreFilter: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the option labels will be rendered with `v-html` instead of `v-text`. */
    labelHtml: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The maximum number of selectable options. */
    maxSelections: {
        type: NumberConstructor;
        default: null;
    };
    /** The controlled value of the combobox. */
    modelValue: {
        type: (ObjectConstructor | NumberConstructor | StringConstructor)[];
        default: null;
    };
    /** When `true`, multiple options are allowed. */
    multiple: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Key of the option's label in the option's object. */
    optionLabel: {
        type: StringConstructor;
        default: string;
    };
    /** Array of option objects */
    options: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** Key of the option's value in the option's object. */
    optionValue: {
        type: StringConstructor;
        default: string;
    };
    /** The delimiter used to split pasted text into multiple tags. Only used when `taggable` is `true`. */
    pasteDelimiter: {
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
    /** When `true`, the options will be searchable. */
    searchable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Determines if the dropdown should open */
    shouldOpenDropdown: {
        type: FunctionConstructor;
        default: () => boolean;
    };
    /** Controls the size of the combobox. <br><br> Options: `xs`, `sm`, `base`, `lg`, `xl` */
    size: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, additional options can be added by typing in the search input and pressing enter. */
    taggable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Controls the appearance of the combobox. <br><br> Options: `default`, `filled`, `ghost`, `subtle` */
    variant: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{
    onSearch?: ((...args: any[]) => any) | undefined;
    "onUpdate:modelValue"?: ((...args: any[]) => any) | undefined;
    onSelected?: ((...args: any[]) => any) | undefined;
    onAdded?: ((...args: any[]) => any) | undefined;
}>, {
    size: string;
    variant: string;
    icon: string;
    disabled: boolean;
    modelValue: string | number | Record<string, any>;
    placeholder: string;
    multiple: boolean;
    align: string;
    readOnly: boolean;
    options: unknown[];
    adaptiveWidth: boolean;
    clearable: boolean;
    closeOnSelect: boolean;
    discreteFocusOutline: boolean;
    ignoreFilter: boolean;
    labelHtml: boolean;
    maxSelections: number;
    optionLabel: string;
    optionValue: string;
    pasteDelimiter: string;
    searchable: boolean;
    shouldOpenDropdown: Function;
    taggable: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    'selected-option'?: ((props: {
        option: any;
    }) => any) | undefined;
} & {
    'no-options'?: ((props: {
        searchQuery: string;
    }) => any) | undefined;
} & {
    option?: ((props: any) => any) | undefined;
} & {
    'selected-options'?: ((props: {
        disabled: boolean;
        readOnly: boolean;
        getOptionLabel: (option: any) => any;
        getOptionValue: (option: any) => any;
        labelHtml: boolean;
        deselect: typeof deselect;
    }) => any) | undefined;
};
declare function focus(): void;
declare function deselect(option: any): void;
