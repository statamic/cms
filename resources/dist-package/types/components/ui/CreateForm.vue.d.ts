declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The title displayed at the top of the form. */
    title: {
        type: StringConstructor;
        required: true;
    };
    /** Optional subtitle displayed below the title. */
    subtitle: {
        type: StringConstructor;
        default: null;
    };
    /** Icon name to display next to the title. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** Text for the submit button. Defaults to the title if not provided. */
    submitText: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the submit button shows a loading state. */
    loading: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The URL for form data to be submitted to. */
    route: {
        type: StringConstructor;
        required: true;
    };
    /** Instructions for the title field. */
    titleInstructions: {
        type: StringConstructor;
        default: null;
    };
    /** Instructions for the handle field. */
    handleInstructions: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the handle field is not displayed. */
    withoutHandle: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The title displayed at the top of the form. */
    title: {
        type: StringConstructor;
        required: true;
    };
    /** Optional subtitle displayed below the title. */
    subtitle: {
        type: StringConstructor;
        default: null;
    };
    /** Icon name to display next to the title. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** Text for the submit button. Defaults to the title if not provided. */
    submitText: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the submit button shows a loading state. */
    loading: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The URL for form data to be submitted to. */
    route: {
        type: StringConstructor;
        required: true;
    };
    /** Instructions for the title field. */
    titleInstructions: {
        type: StringConstructor;
        default: null;
    };
    /** Instructions for the handle field. */
    handleInstructions: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the handle field is not displayed. */
    withoutHandle: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{}>, {
    icon: string;
    loading: boolean;
    subtitle: string;
    submitText: string;
    titleInstructions: string;
    handleInstructions: string;
    withoutHandle: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
} & {
    footer?: ((props: {}) => any) | undefined;
};
