declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    condition: ObjectConstructor;
    suggestableFields: ArrayConstructor;
    excludeHandle: StringConstructor;
    excludeOperators: {
        type: ArrayConstructor;
        default: () => never[];
    };
    size: StringConstructor;
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "update:condition": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    condition: ObjectConstructor;
    suggestableFields: ArrayConstructor;
    excludeHandle: StringConstructor;
    excludeOperators: {
        type: ArrayConstructor;
        default: () => never[];
    };
    size: StringConstructor;
}>> & Readonly<{
    "onUpdate:condition"?: ((...args: any[]) => any) | undefined;
}>, {
    excludeOperators: unknown[];
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    'field-option'?: ((props: any) => any) | undefined;
} & {
    'field-selected'?: ((props: {
        option: any;
        field: unknown;
    }) => any) | undefined;
};
