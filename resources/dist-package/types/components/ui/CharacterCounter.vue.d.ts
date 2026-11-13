declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The text to count characters from */
    text: {
        type: StringConstructor;
        default: string;
    };
    /** The maximum number of characters allowed */
    limit: {
        type: NumberConstructor;
        default: null;
    };
    /** Number of characters remaining before showing the countdown number (default: 20) */
    dangerZone: {
        type: NumberConstructor;
        default: number;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The text to count characters from */
    text: {
        type: StringConstructor;
        default: string;
    };
    /** The maximum number of characters allowed */
    limit: {
        type: NumberConstructor;
        default: null;
    };
    /** Number of characters remaining before showing the countdown number (default: 20) */
    dangerZone: {
        type: NumberConstructor;
        default: number;
    };
}>> & Readonly<{}>, {
    text: string;
    limit: number;
    dangerZone: number;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
