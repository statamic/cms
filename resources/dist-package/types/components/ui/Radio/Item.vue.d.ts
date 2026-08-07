declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Description text to display below the label */
    description: {
        type: StringConstructor;
        default: null;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Label text to display next to the radio button */
    label: {
        type: StringConstructor;
        default: null;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Value of the radio button */
    value: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor)[];
        required: true;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Description text to display below the label */
    description: {
        type: StringConstructor;
        default: null;
    };
    disabled: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Label text to display next to the radio button */
    label: {
        type: StringConstructor;
        default: null;
    };
    readOnly: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Value of the radio button */
    value: {
        type: (NumberConstructor | StringConstructor | BooleanConstructor)[];
        required: true;
    };
}>> & Readonly<{}>, {
    label: string;
    description: string;
    disabled: boolean;
    readOnly: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
