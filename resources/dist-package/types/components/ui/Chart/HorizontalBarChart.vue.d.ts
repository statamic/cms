type Item = {
    key?: string;
    label: string;
    count: number;
    percent: number;
    clickable?: boolean;
    icon?: string;
};
type __VLS_Props = {
    /** Accessible label summarizing the chart's results. */
    accessibleLabel: string;
    /** Colours every row as this item, for rows that break one item down. */
    focusedIndex?: number | null;
    /** The chart rows. Each item supports `label`, `percent`, `count`, `clickable`, and `icon`. */
    items?: Item[];
    /** Whether values are displayed as percentages or response counts. */
    metric?: 'percent' | 'count';
    /** Places the metric before or after each bar. */
    metricPosition?: 'start' | 'end';
    /** Places the marker before or after the label. */
    markerPosition?: 'before-label' | 'after-label';
    /** Number of respondents for the field. Use it for totals instead of summing `items`, which counts choices rather than people for multi-select fields and only holds the "Other" breakdown on the drilldown page. */
    responses?: number | null;
    /** Whether to show item labels. */
    showLabel?: boolean;
    /** Whether to show a marker before each label. */
    showMarker?: boolean;
};
declare var __VLS_14: {
    item: Item;
    index: number;
}, __VLS_21: {
    item: Item;
    index: number;
}, __VLS_28: {
    item: Item;
    index: number;
    width: string;
};
type __VLS_Slots = {} & {
    marker?: (props: typeof __VLS_14) => any;
} & {
    marker?: (props: typeof __VLS_21) => any;
} & {
    bar?: (props: typeof __VLS_28) => any;
};
declare const __VLS_base: import("vue").DefineComponent<__VLS_Props, {}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    select: (item: Item, index: number) => any;
}, string, import("vue").PublicProps, Readonly<__VLS_Props> & Readonly<{
    onSelect?: ((item: Item, index: number) => any) | undefined;
}>, {
    items: Item[];
    metric: "percent" | "count";
    focusedIndex: number | null;
    metricPosition: "start" | "end";
    markerPosition: "before-label" | "after-label";
    responses: number | null;
    showLabel: boolean;
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
