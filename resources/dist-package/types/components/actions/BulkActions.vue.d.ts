declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    url: {
        type: StringConstructor;
        required: true;
    };
    selections: {
        type: ArrayConstructor;
        required: true;
    };
    context: {
        type: ObjectConstructor;
        default: () => void;
    };
    showAlways: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    completed: (...args: any[]) => void;
    started: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    url: {
        type: StringConstructor;
        required: true;
    };
    selections: {
        type: ArrayConstructor;
        required: true;
    };
    context: {
        type: ObjectConstructor;
        default: () => void;
    };
    showAlways: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    onCompleted?: ((...args: any[]) => any) | undefined;
    onStarted?: ((...args: any[]) => any) | undefined;
}>, {
    context: Record<string, any>;
    showAlways: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {
        actions: any;
        loading: any;
    }) => any) | undefined;
};
