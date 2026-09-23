type __VLS_Props = {
    /** Title displayed at the top of the stack */
    title?: string;
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon?: string | null;
    /** Whether the close button should be shown */
    showCloseButton?: boolean;
};
declare var __VLS_11: {
    close: (() => void) | undefined;
};
type __VLS_Slots = {} & {
    actions?: (props: typeof __VLS_11) => any;
};
declare const __VLS_base: import("vue").DefineComponent<__VLS_Props, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<__VLS_Props> & Readonly<{}>, {
    showCloseButton: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
declare const _default: typeof __VLS_export;
export default _default;
type __VLS_WithSlots<T, S> = T & {
    new (): {
        $slots: S;
    };
};
