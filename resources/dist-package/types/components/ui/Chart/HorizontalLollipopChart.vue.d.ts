type Item = {
    key?: string;
    label: string;
    count: number;
    percent: number;
    rank?: number;
    clickable?: boolean;
};
type __VLS_Props = {
    /** Accessible label summarizing the chart's results. */
    accessibleLabel: string;
    /** Colours every row as this item, for rows that break one item down. */
    focusedIndex?: number | null;
    /** The chart rows. Each item supports `label`, `percent`, `count`, `rank`, and `clickable`. */
    items?: Item[];
    /** Whether values are displayed as percentages or response counts. */
    metric?: 'percent' | 'count';
    /** Number of respondents for the field. Use it for totals instead of summing `items`, which counts choices rather than people for multi-select fields and only holds the "Other" breakdown on the drilldown page. */
    responses?: number | null;
    /** Whether to show a marker before each label. */
    showMarker?: boolean;
};
declare var __VLS_9: {
    item: Item;
    index: number;
}, __VLS_11: {
    item: Item;
    index: number;
};
type __VLS_Slots = {} & {
    marker?: (props: typeof __VLS_9) => any;
} & {
    endpoint?: (props: typeof __VLS_11) => any;
};
declare const __VLS_base: import("vue").DefineComponent<__VLS_Props, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    select: (item: Item, index: number) => any;
}, string, import("vue").PublicProps, Readonly<__VLS_Props> & Readonly<{
    onSelect?: ((item: Item, index: number) => any) | undefined;
}>, {
    items: Item[];
    metric: "percent" | "count";
    focusedIndex: number | null;
    responses: number | null;
    showMarker: boolean;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
declare const _default: typeof __VLS_export;
export default _default;
type __VLS_WithSlots<T, S> = T & {
    new (): {
        $slots: S;
    };
};
