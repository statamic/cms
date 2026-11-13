declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** When `true`, shows the totals (eg. 1-10 of 50) */
    showTotals: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The number of items per page */
    perPage: {
        type: NumberConstructor;
    };
    /** The `meta` object from a Laravel API resource */
    resourceMeta: {
        type: ObjectConstructor;
        required: true;
    };
    /** When `true`, scrolls to the top when changing pages */
    scrollToTop: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows individual page number buttons */
    showPageLinks: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows the "per page" dropdown */
    showPerPageSelector: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    "page-selected": (...args: any[]) => void;
    "per-page-changed": (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** When `true`, shows the totals (eg. 1-10 of 50) */
    showTotals: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The number of items per page */
    perPage: {
        type: NumberConstructor;
    };
    /** The `meta` object from a Laravel API resource */
    resourceMeta: {
        type: ObjectConstructor;
        required: true;
    };
    /** When `true`, scrolls to the top when changing pages */
    scrollToTop: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows individual page number buttons */
    showPageLinks: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows the "per page" dropdown */
    showPerPageSelector: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    "onPage-selected"?: ((...args: any[]) => any) | undefined;
    "onPer-page-changed"?: ((...args: any[]) => any) | undefined;
}>, {
    showTotals: boolean;
    scrollToTop: boolean;
    showPageLinks: boolean;
    showPerPageSelector: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>;
