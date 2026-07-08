declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The preferred alignment against the trigger. May change when collisions occur. Options: `start`, `center`, `end` */
    align: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, an arrow is displayed near the trigger. */
    arrow: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the internal padding of the popover is removed. */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The distance in pixels from the trigger */
    offset: {
        type: NumberConstructor;
        default: number;
    };
    /** The preferred side of the trigger to render against when open. Options: `top`, `bottom`, `left`, `right` */
    side: {
        type: StringConstructor;
        default: string;
    };
    /** The controlled open state of the popover. */
    open: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, clicking outside the modal will dismiss it. */
    dismissible: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:open": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The preferred alignment against the trigger. May change when collisions occur. Options: `start`, `center`, `end` */
    align: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, an arrow is displayed near the trigger. */
    arrow: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, the internal padding of the popover is removed. */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The distance in pixels from the trigger */
    offset: {
        type: NumberConstructor;
        default: number;
    };
    /** The preferred side of the trigger to render against when open. Options: `top`, `bottom`, `left`, `right` */
    side: {
        type: StringConstructor;
        default: string;
    };
    /** The controlled open state of the popover. */
    open: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, clicking outside the modal will dismiss it. */
    dismissible: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:open"?: ((...args: any[]) => any) | undefined;
}>, {
    inset: boolean;
    align: string;
    open: boolean;
    side: string;
    dismissible: boolean;
    offset: number;
    arrow: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    trigger?: ((props: {}) => any) | undefined;
} & {
    default?: ((props: {
        open: boolean;
        close: () => void;
    }) => any) | undefined;
} & {
    close?: ((props: {
        open: boolean;
        close: () => void;
    }) => any) | undefined;
};
