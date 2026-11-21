declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    enabled: {
        type: BooleanConstructor;
        default: boolean;
        required: true;
    };
    url: {
        type: StringConstructor;
        required: false;
    };
    targets: {
        type: ArrayConstructor;
        default: () => never[];
        required: true;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    opened: (...args: any[]) => void;
    closed: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    enabled: {
        type: BooleanConstructor;
        default: boolean;
        required: true;
    };
    url: {
        type: StringConstructor;
        required: false;
    };
    targets: {
        type: ArrayConstructor;
        default: () => never[];
        required: true;
    };
}>> & Readonly<{
    onOpened?: ((...args: any[]) => any) | undefined;
    onClosed?: ((...args: any[]) => any) | undefined;
}>, {
    targets: unknown[];
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {}) => any) | undefined;
} & {
    buttons?: ((props: {}) => any) | undefined;
};
