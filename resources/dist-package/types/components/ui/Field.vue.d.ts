declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** When `true`, the field is styled as a configuration field with a two-column grid layout. */
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Badge text to display next to the label. */
    badge: {
        type: StringConstructor;
        default: string;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Error message to display below the field. */
    error: {
        type: StringConstructor;
    };
    /** Object or array of error messages to display below the field. */
    errors: {
        type: ObjectConstructor;
    };
    /** When `true`, forces the field to use full width even when `asConfig` is enabled. */
    fullWidthSetting: {
        type: BooleanConstructor;
        default: boolean;
    };
    id: {
        type: StringConstructor;
    };
    /** Instructions text to display above or below the label. Supports Markdown. */
    instructions: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, displays instructions below the control instead of below the label. */
    instructionsBelow: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Label text for the field. */
    label: {
        type: StringConstructor;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** When `true`, the field is styled as a configuration field with a two-column grid layout. */
    inline: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Badge text to display next to the label. */
    badge: {
        type: StringConstructor;
        default: string;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Error message to display below the field. */
    error: {
        type: StringConstructor;
    };
    /** Object or array of error messages to display below the field. */
    errors: {
        type: ObjectConstructor;
    };
    /** When `true`, forces the field to use full width even when `asConfig` is enabled. */
    fullWidthSetting: {
        type: BooleanConstructor;
        default: boolean;
    };
    id: {
        type: StringConstructor;
    };
    /** Instructions text to display above or below the label. Supports Markdown. */
    instructions: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, displays instructions below the control instead of below the label. */
    instructionsBelow: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Label text for the field. */
    label: {
        type: StringConstructor;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    required: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{}>, {
    required: boolean;
    disabled: boolean;
    inline: boolean;
    readOnly: boolean;
    badge: string;
    fullWidthSetting: boolean;
    instructions: string;
    instructionsBelow: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    label?: ((props: {}) => any) | undefined;
} & {
    actions?: ((props: {}) => any) | undefined;
} & {
    label?: ((props: {}) => any) | undefined;
} & {
    default?: ((props: {}) => any) | undefined;
};
