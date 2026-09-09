declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** When `true`, the modal's backdrop will be blurred */
    blur: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Title displayed at the top of the modal */
    title: {
        type: StringConstructor;
        default: string;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: (StringConstructor | null)[];
        default: null;
    };
    /** The controlled open state of the modal. */
    open: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Callback that fires before the modal closes. */
    beforeClose: {
        type: FunctionConstructor;
        default: () => boolean;
    };
    /** When `true`, clicking outside the modal will dismiss it. */
    dismissible: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {
    open: typeof open;
    close: typeof close;
    runCloseCallback: typeof runCloseCallback;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:open": (...args: any[]) => void;
    opened: (...args: any[]) => void;
    dismissed: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** When `true`, the modal's backdrop will be blurred */
    blur: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Title displayed at the top of the modal */
    title: {
        type: StringConstructor;
        default: string;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: (StringConstructor | null)[];
        default: null;
    };
    /** The controlled open state of the modal. */
    open: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Callback that fires before the modal closes. */
    beforeClose: {
        type: FunctionConstructor;
        default: () => boolean;
    };
    /** When `true`, clicking outside the modal will dismiss it. */
    dismissible: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:open"?: ((...args: any[]) => any) | undefined;
    onOpened?: ((...args: any[]) => any) | undefined;
    onDismissed?: ((...args: any[]) => any) | undefined;
}>, {
    blur: boolean;
    title: string;
    icon: string | null;
    open: boolean;
    beforeClose: Function;
    dismissible: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    trigger?: ((props: {}) => any) | undefined;
} & {
    default?: ((props: {}) => any) | undefined;
} & {
    footer?: ((props: {}) => any) | undefined;
};
declare function open(): void;
declare function close(): void;
declare function runCloseCallback(): boolean;
