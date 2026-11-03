type Item = {
    key?: string;
    label: string;
    count: number;
    percent: number;
};
type __VLS_Props = {
    /** Accessible label summarizing the chart's results. */
    accessibleLabel: string;
    /** Accepted for parity with the other charts. Columns all share one colour, so it has no visual effect. */
    focusedIndex?: number | null;
    /** The chart columns. Each item supports `label`, `percent`, and `count`. */
    items?: Item[];
    /** The largest value represented by a full-height column. */
    maxValue?: number | null;
    /** Whether values are displayed as percentages or response counts. */
    metric?: 'percent' | 'count';
    /** Number of respondents for the field. Use it for totals instead of summing `items`, which counts choices rather than people for multi-select fields and only holds the "Other" breakdown on the drilldown page. */
    responses?: number | null;
};
declare const __VLS_export: import("vue").DefineComponent<__VLS_Props, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {}, string, import("vue").PublicProps, Readonly<__VLS_Props> & Readonly<{}>, {
    items: Item[];
    maxValue: number | null;
    metric: "percent" | "count";
    focusedIndex: number | null;
    responses: number | null;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const _default: typeof __VLS_export;
export default _default;
