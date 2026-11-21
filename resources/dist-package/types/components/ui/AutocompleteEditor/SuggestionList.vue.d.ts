declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    items: {
        type: ArrayConstructor;
        default: () => never[];
    };
    command: {
        type: FunctionConstructor;
        required: true;
    };
}>, {
    onKeyDown: typeof onKeyDown;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    items: {
        type: ArrayConstructor;
        default: () => never[];
    };
    command: {
        type: FunctionConstructor;
        required: true;
    };
}>> & Readonly<{}>, {
    items: unknown[];
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
declare function onKeyDown({ event }: {
    event: any;
}): boolean;
