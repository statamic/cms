export const injectTabContext: string | ((context: any) => void);
export const provideTabContext: string | ((context: any) => void);
declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    tab: {
        type: ObjectConstructor;
        required: true;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    tab: {
        type: ObjectConstructor;
        required: true;
    };
}>> & Readonly<{}>, {}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>, {
    default?: (props: {}) => any;
}>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
