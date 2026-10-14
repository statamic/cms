declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    collapsed: BooleanConstructor;
    enabled: BooleanConstructor;
    hasError: BooleanConstructor;
    handleClass: StringConstructor;
    context: ObjectConstructor;
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    expanded: (...args: any[]) => void;
    collapsed: (...args: any[]) => void;
    removed: (...args: any[]) => void;
    duplicated: (...args: any[]) => void;
    "update:enabled": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    collapsed: BooleanConstructor;
    enabled: BooleanConstructor;
    hasError: BooleanConstructor;
    handleClass: StringConstructor;
    context: ObjectConstructor;
}>> & Readonly<{
    onExpanded?: ((...args: any[]) => any) | undefined;
    onCollapsed?: ((...args: any[]) => any) | undefined;
    onRemoved?: ((...args: any[]) => any) | undefined;
    onDuplicated?: ((...args: any[]) => any) | undefined;
    "onUpdate:enabled"?: ((...args: any[]) => any) | undefined;
}>, {
    enabled: boolean;
    collapsed: boolean;
    hasError: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    header?: ((props: {}) => any) | undefined;
} & {
    default?: ((props: {}) => any) | undefined;
};
