declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** Icon name. Will display after the text. [Browse available icons](/?path=/story/components-icon--all-icons) */
    appendIcon: {
        type: StringConstructor;
        default: null;
    };
    /** URL for the append icon button to link to */
    appendHref: {
        type: StringConstructor;
        default: null;
    };
    /** Text to display in the header */
    text: {
        type: StringConstructor;
        default: null;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: StringConstructor;
        default: null;
    };
    /** Icon name. Will display after the text. [Browse available icons](/?path=/story/components-icon--all-icons) */
    appendIcon: {
        type: StringConstructor;
        default: null;
    };
    /** URL for the append icon button to link to */
    appendHref: {
        type: StringConstructor;
        default: null;
    };
    /** Text to display in the header */
    text: {
        type: StringConstructor;
        default: null;
    };
}>> & Readonly<{}>, {
    text: string;
    icon: string;
    appendIcon: string;
    appendHref: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
};
