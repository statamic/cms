declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
declare const __VLS_base: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    category: {
        type: StringConstructor;
    };
    icon: {
        type: StringConstructor;
    };
    when: {
        type: FunctionConstructor;
    };
    text: {
        type: (ArrayConstructor | StringConstructor)[];
    };
    url: {
        type: StringConstructor;
    };
    action: {
        type: FunctionConstructor;
    };
    openNewTab: {
        type: BooleanConstructor;
    };
    badge: {
        type: StringConstructor;
    };
    keys: {
        type: StringConstructor;
    };
    trackRecent: {
        type: BooleanConstructor;
    };
    prioritize: {
        type: BooleanConstructor;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    category: {
        type: StringConstructor;
    };
    icon: {
        type: StringConstructor;
    };
    when: {
        type: FunctionConstructor;
    };
    text: {
        type: (ArrayConstructor | StringConstructor)[];
    };
    url: {
        type: StringConstructor;
    };
    action: {
        type: FunctionConstructor;
    };
    openNewTab: {
        type: BooleanConstructor;
    };
    badge: {
        type: StringConstructor;
    };
    keys: {
        type: StringConstructor;
    };
    trackRecent: {
        type: BooleanConstructor;
    };
    prioritize: {
        type: BooleanConstructor;
    };
}>> & Readonly<{}>, {
    openNewTab: boolean;
    trackRecent: boolean;
    prioritize: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
type __VLS_Slots = {
    default?: ((props: {
        category: string | undefined;
        icon: string | undefined;
        when: Function | undefined;
        text: string | unknown[] | undefined;
        url: string | undefined;
        action: Function | undefined;
        badge: string | undefined;
        keys: string | undefined;
    }) => any) | undefined;
};
