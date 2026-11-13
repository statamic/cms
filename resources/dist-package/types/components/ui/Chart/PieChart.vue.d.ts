type Item = {
    key?: string;
    label: string;
    count: number;
    percent: number;
    clickable?: boolean;
    icon?: string;
    image?: string;
    badge?: string;
};
type __VLS_Props = {
    /** Accessible label summarizing the chart's results. */
    accessibleLabel: string;
    /** Highlights one segment while muting the others. */
    focusedIndex?: number | null;
    /** Up to four legend items. Each item supports `label`, `percent`, `count`, `clickable`, `icon`, `image`, and `badge`. */
    items?: Item[];
    /** Whether values are displayed as percentages or response counts. */
    metric?: 'percent' | 'count';
    /** Number of respondents for the field. Use it for totals instead of summing `items`, which counts choices rather than people for multi-select fields and only holds the "Other" breakdown on the drilldown page. */
    responses?: number | null;
    /** Optional segment data when the legend describes a focused segment. */
    segments?: Item[] | null;
};
declare var __VLS_24: {
    item: Item;
    index: number;
};
type __VLS_Slots = {} & {
    marker?: (props: typeof __VLS_24) => any;
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
    segments: Item[] | null;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, false, {}, any>;
declare const __VLS_export: __VLS_WithSlots<typeof __VLS_base, __VLS_Slots>;
declare const _default: typeof __VLS_export;
export default _default;
type __VLS_WithSlots<T, S> = T & {
    new (): {
        $slots: S;
    };
};
