type OptionValue = string | number;
type ModelValue = OptionValue | OptionValue[] | null;
interface Option {
    /** Icon name displayed next to the label. */
    icon?: string;
    /** Image URL displayed next to the label. */
    image?: string;
    /** Set on the "Add" option shown while searching a `taggable` combobox. */
    create?: boolean;
    /** The label and value live under the keys set by `optionLabel` and `optionValue`. */
    [key: string]: any;
}
interface Props {
    /** When `true`, the dropdown will expand to fit longer option labels. Not recommended for large datasets. */
    adaptiveWidth?: boolean;
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align?: 'start' | 'center' | 'end';
    /** When `true`, the selected value will be clearable. */
    clearable?: boolean;
    /** When `true`, the options dropdown will close after selecting an option. */
    closeOnSelect?: boolean;
    disabled?: boolean;
    /** When `true`, a `taggable` combobox only shows its search input while the dropdown is open, so the selected option is displayed while it's closed. */
    hideInputWhenClosed?: boolean;
    /** When `true`, the focus outline will be more discrete. */
    discreteFocusOutline?: boolean;
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon?: string | null;
    /** ID attribute for the input element. Inherits from parent `Field` when omitted. */
    id?: string | null;
    /** When `true`, the Combobox will avoid filtering options, allowing you to handle filtering yourself by listening to the `search` event and updating the `options` prop. */
    ignoreFilter?: boolean;
    /** When `true`, the option labels will be rendered with `v-html` instead of `v-text`. */
    labelHtml?: boolean;
    /** The maximum number of selectable options. */
    maxSelections?: number | null;
    /** The controlled value of the combobox. A single option value, or an array of option values when `multiple` is `true`. */
    modelValue?: ModelValue;
    /** When `true`, multiple options are allowed. */
    multiple?: boolean;
    /** Key of the option's label in the option's object. */
    optionLabel?: string;
    /** Array of option objects, like `{ label: 'Apple', value: 'apple' }`. The label and value keys can be changed with `optionLabel` and `optionValue`. Options may also have an `icon` name or an `image` URL. */
    options?: Option[];
    /** Key of the option's value in the option's object. */
    optionValue?: string;
    /** The delimiter used to split pasted text into multiple tags. Only used when `taggable` is `true`. */
    pasteDelimiter?: string;
    placeholder?: string;
    readOnly?: boolean;
    /** When `true`, the options will be searchable. */
    searchable?: boolean;
    /** Keys of the option object to search against. Defaults to just `optionLabel`. */
    searchKeys?: string[] | null;
    /** Determines if the dropdown should open */
    shouldOpenDropdown?: (open: boolean) => boolean;
    /** Controls the size of the combobox. <br><br> Options: `xs`, `sm`, `base`, `lg`, `xl` */
    size?: 'xs' | 'sm' | 'base' | 'lg' | 'xl';
    /** When `true`, additional options can be added by typing in the search input and pressing enter. */
    taggable?: boolean;
    /** Controls the appearance of the combobox. <br><br> Options: `default`, `filled`, `ghost`, `subtle` */
    variant?: 'default' | 'filled' | 'ghost' | 'subtle';
    /** When `true`, skip the elevated z-index override while a modal/stack is open. Use when this combobox can remain open behind an unrelated overlay (e.g. a confirmation modal). */
    excludeZManipulation?: boolean;
}
declare function deselect(value: OptionValue): void;
declare function focus(): void;
declare var __VLS_41: {
    option: Option;
}, __VLS_89: {
    searchQuery: string;
}, __VLS_105: {
    [key: string]: any;
    /** Icon name displayed next to the label. */
    icon?: string;
    /** Image URL displayed next to the label. */
    image?: string;
    /** Set on the "Add" option shown while searching a `taggable` combobox. */
    create?: boolean;
}, __VLS_112: {
    disabled: boolean;
    readOnly: boolean;
    getOptionLabel: (option: Option) => string;
    getOptionPlainLabel: (option: Option) => string;
    getOptionValue: (option: Option) => OptionValue;
    labelHtml: boolean;
    deselect: typeof deselect;
};
type __VLS_Slots = {} & {
    'selected-option'?: (props: typeof __VLS_41) => any;
} & {
    'no-options'?: (props: typeof __VLS_89) => any;
} & {
    option?: (props: typeof __VLS_105) => any;
} & {
    'selected-options'?: (props: typeof __VLS_112) => any;
};
declare const __VLS_base: import("vue").DefineComponent<Props, {
    searchQuery: import("vue").Ref<string, string>;
    filteredOptions: import("vue").ComputedRef<Option[]>;
    focus: typeof focus;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    search: (query: string, loading: () => void) => any;
    selected: (value: OptionValue) => any;
    "update:modelValue": (value: ModelValue) => any;
    added: (value: OptionValue) => any;
}, string, import("vue").PublicProps, Readonly<Props> & Readonly<{
    onSearch?: ((query: string, loading: () => void) => any) | undefined;
    onSelected?: ((value: OptionValue) => any) | undefined;
    "onUpdate:modelValue"?: ((value: ModelValue) => any) | undefined;
    onAdded?: ((value: OptionValue) => any) | undefined;
}>, {
    id: string | null;
    options: Option[];
    size: "xs" | "sm" | "base" | "lg" | "xl";
    variant: "default" | "filled" | "ghost" | "subtle";
    icon: string | null;
    disabled: boolean;
    readOnly: boolean;
    modelValue: ModelValue;
    placeholder: string;
    clearable: boolean;
    align: "start" | "center" | "end";
    excludeZManipulation: boolean;
    multiple: boolean;
    adaptiveWidth: boolean;
    closeOnSelect: boolean;
    hideInputWhenClosed: boolean;
    discreteFocusOutline: boolean;
    ignoreFilter: boolean;
    labelHtml: boolean;
    maxSelections: number | null;
    optionLabel: string;
    optionValue: string;
    pasteDelimiter: string;
    searchable: boolean;
    searchKeys: string[] | null;
    shouldOpenDropdown: (open: boolean) => boolean;
    taggable: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
declare const _default: typeof __VLS_export;
export default _default;
type __VLS_WithSlots<T, S> = T & {
    new (): {
        $slots: S;
    };
};
