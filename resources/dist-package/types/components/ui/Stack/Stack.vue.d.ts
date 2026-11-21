declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** Title displayed at the top of the stack */
    title: {
        type: StringConstructor;
        default: string;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: (StringConstructor | null)[];
        default: null;
    };
    /** The controlled open state of the stack. */
    open: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Callback that fires before the stack closes. */
    beforeClose: {
        type: FunctionConstructor;
        default: () => boolean;
    };
    /** Controls the size of the stack. Options: `narrow`, `half`, `full` */
    size: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the internal padding of the stack content is removed. */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the close button is shown in the top-right corner of the stack. */
    showCloseButton: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `false`, the logic for wrapping the slot in a Content component is ignored and the slot will not be wrapped. */
    wrapSlot: {
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
    closed: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** Title displayed at the top of the stack */
    title: {
        type: StringConstructor;
        default: string;
    };
    /** Icon name. [Browse available icons](/?path=/story/components-icon--all-icons) */
    icon: {
        type: (StringConstructor | null)[];
        default: null;
    };
    /** The controlled open state of the stack. */
    open: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Callback that fires before the stack closes. */
    beforeClose: {
        type: FunctionConstructor;
        default: () => boolean;
    };
    /** Controls the size of the stack. Options: `narrow`, `half`, `full` */
    size: {
        type: StringConstructor;
        default: null;
    };
    /** When `true`, the internal padding of the stack content is removed. */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the close button is shown in the top-right corner of the stack. */
    showCloseButton: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `false`, the logic for wrapping the slot in a Content component is ignored and the slot will not be wrapped. */
    wrapSlot: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:open"?: ((...args: any[]) => any) | undefined;
    onOpened?: ((...args: any[]) => any) | undefined;
    onClosed?: ((...args: any[]) => any) | undefined;
}>, {
    title: string;
    size: string;
    icon: string | null;
    inset: boolean;
    open: boolean;
    beforeClose: Function;
    showCloseButton: boolean;
    wrapSlot: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    trigger?: ((props: {}) => any) | undefined;
} & {
    'header-actions'?: ((props: {}) => any) | undefined;
} & {
    default?: ((props: {
        close: typeof close;
    }) => any) | undefined;
} & {
    default?: ((props: {
        close: typeof close;
    }) => any) | undefined;
} & {
    default?: ((props: {
        close: typeof close;
    }) => any) | undefined;
} & {
    default?: ((props: {
        close: typeof close;
    }) => any) | undefined;
} & {
    'footer-start'?: ((props: {}) => any) | undefined;
} & {
    'footer-end'?: ((props: {}) => any) | undefined;
};
declare function open(): void;
declare function close(): void;
declare function runCloseCallback(): boolean;
