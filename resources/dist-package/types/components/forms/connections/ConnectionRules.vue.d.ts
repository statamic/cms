declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    conditions: {
        type: ArrayConstructor;
        required: true;
    };
    alwaysLabel: {
        type: StringConstructor;
        default: () => any;
    };
    ifLabel: {
        type: StringConstructor;
        default: () => any;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:conditions": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    conditions: {
        type: ArrayConstructor;
        required: true;
    };
    alwaysLabel: {
        type: StringConstructor;
        default: () => any;
    };
    ifLabel: {
        type: StringConstructor;
        default: () => any;
    };
}>> & Readonly<{
    "onUpdate:conditions"?: ((...args: any[]) => any) | undefined;
}>, {
    alwaysLabel: string;
    ifLabel: string;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    then?: ((props: {}) => any) | undefined;
};
