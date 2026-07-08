declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, an arrow is displayed near the trigger. */
    arrow: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The delay in milliseconds before the hover card opens. */
    delay: {
        type: NumberConstructor;
        default: number;
    };
    /** When `true`, the internal padding of the hover card is removed. */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The distance in pixels from the trigger. */
    offset: {
        type: NumberConstructor;
        default: number;
    };
    /** The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right` */
    side: {
        type: StringConstructor;
        default: string;
    };
    /** The controlled open state of the hover card. */
    open: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:open": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, an arrow is displayed near the trigger. */
    arrow: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The delay in milliseconds before the hover card opens. */
    delay: {
        type: NumberConstructor;
        default: number;
    };
    /** When `true`, the internal padding of the hover card is removed. */
    inset: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The distance in pixels from the trigger. */
    offset: {
        type: NumberConstructor;
        default: number;
    };
    /** The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right` */
    side: {
        type: StringConstructor;
        default: string;
    };
    /** The controlled open state of the hover card. */
    open: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onUpdate:open"?: ((...args: any[]) => any) | undefined;
}>, {
    inset: boolean;
    align: string;
    delay: number;
    open: boolean;
    side: string;
    offset: number;
    arrow: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    trigger?: ((props: {}) => any) | undefined;
} & {
    default?: ((props: {
        open: boolean;
    }) => any) | undefined;
};
