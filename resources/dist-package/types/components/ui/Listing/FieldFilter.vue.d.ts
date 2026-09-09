declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    config: ObjectConstructor;
    values: ObjectConstructor;
}>, {
    focusAddFieldCombobox: typeof focusAddFieldCombobox;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    changed: (...args: any[]) => void;
    cleared: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    config: ObjectConstructor;
    values: ObjectConstructor;
}>> & Readonly<{
    onChanged?: ((...args: any[]) => any) | undefined;
    onCleared?: ((...args: any[]) => any) | undefined;
}>, {}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
declare function focusAddFieldCombobox(): Promise<void>;
