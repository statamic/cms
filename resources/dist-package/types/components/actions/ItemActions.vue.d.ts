declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    url: {
        type: StringConstructor;
    };
    actions: {
        type: ArrayConstructor;
    };
    context: {
        type: ObjectConstructor;
        default: () => {};
    };
    item: {
        required: true;
    };
    isDirty: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {
    preparedActions: any;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    completed: (...args: any[]) => void;
    started: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    url: {
        type: StringConstructor;
    };
    actions: {
        type: ArrayConstructor;
    };
    context: {
        type: ObjectConstructor;
        default: () => {};
    };
    item: {
        required: true;
    };
    isDirty: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    onCompleted?: ((...args: any[]) => any) | undefined;
    onStarted?: ((...args: any[]) => any) | undefined;
}>, {
    isDirty: boolean;
    context: Record<string, any>;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {
        actions: any;
        loadActions: any;
        loading: any;
        shouldShowSkeleton: any;
    }) => any) | undefined;
};
