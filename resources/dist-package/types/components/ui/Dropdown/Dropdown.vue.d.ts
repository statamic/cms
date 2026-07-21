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
    /** The distance in pixels from the trigger */
    offset: {
        type: NumberConstructor;
        default: number;
    };
    /** The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right` */
    side: {
        type: StringConstructor;
        default: string;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The preferred alignment against the trigger. May change when collisions occur. <br><br> Options: `start`, `center`, `end` */
    align: {
        type: StringConstructor;
        default: string;
    };
    /** The distance in pixels from the trigger */
    offset: {
        type: NumberConstructor;
        default: number;
    };
    /** The preferred side of the trigger to render against when open. <br><br> Options: `top`, `bottom`, `left`, `right` */
    side: {
        type: StringConstructor;
        default: string;
    };
}>> & Readonly<{}>, {
    align: string;
    side: string;
    offset: number;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    trigger?: ((props: {}) => any) | undefined;
} & {
    default?: ((props: {}) => any) | undefined;
};
