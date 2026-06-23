export const injectListingContext: string | ((context: any) => void);
export const provideListingContext: string | ((context: any) => void);
declare const _default: typeof __VLS_export;
export default _default;
declare const __VLS_export: __VLS_WithSlots<import("vue").DefineComponent<import("vue").ExtractPropTypes<{
    /** The URL from which to retrieve results. Either use this or `items`. */
    url: {
        type: StringConstructor;
    };
    /** Array of items to display in the listing. When provided, sorting and filtering is done client-side. Either use this or `url`. */
    items: {
        type: ArrayConstructor;
    };
    /** When `true`, allows users to save and load column/filter presets. */
    allowPresets: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, bulk actions are available when items are selected. */
    allowBulkActions: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The URL from which to retrieve actions. */
    actionUrl: {
        type: StringConstructor;
    };
    /** Extra data to pass to the server when using actions. */
    actionContext: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** When `true`, enables the action dropdown while reordering is enabled. */
    allowActionsWhileReordering: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, adds drag handles to the rows. */
    reorderable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Any preferences (preferred columns, etc) will be saved nested under this. */
    preferencesPrefix: {
        type: StringConstructor;
    };
    /** The columns to display. Can be array of strings or column definitions. */
    columns: {
        type: ArrayConstructor;
    };
    /** When `true`, users can show/hide columns. */
    allowCustomizingColumns: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Defines the sort column. */
    sortColumn: {
        type: StringConstructor;
        default: string;
    };
    /** Defines the sort direction. Defaults to `asc` for most fields, `desc` for dates. <br><br> Options: `asc`, `desc` */
    sortDirection: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, columns can be sorted by clicking on headers. */
    sortable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Array of checked item IDs. */
    selections: {
        type: ArrayConstructor;
    };
    /** Maximum number of items that can be selected. */
    maxSelections: {
        type: NumberConstructor;
        default: number;
    };
    /** When `true`, adds the parameters to the current URL. */
    pushQuery: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Extra data to send to the AJAX URL. */
    additionalParameters: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** When `true`, displays a search input for filtering items. */
    allowSearch: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The search query value. */
    searchQuery: {
        type: StringConstructor;
        default: null;
    };
    /** Array of filter definitions. You can get this by doing `Scope::filters($name, $context)` */
    filters: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** A function that returns array of filter values to be activated when reordering is enabled. */
    filtersForReordering: {
        type: FunctionConstructor;
        default: null;
    };
    /** Number of items to display per page. */
    perPage: {
        type: NumberConstructor;
        default: () => any;
    };
    /** When `true`, shows the totals in the paginator. e.g. "1-5 of 10" */
    showPaginationTotals: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows the page links. e.g. 1,2,3,4. With this disabled you'll just get the prev/next arrows. */
    showPaginationPageLinks: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows the per page dropdown. */
    showPaginationPerPageSelector: {
        type: BooleanConstructor;
        default: boolean;
    };
}>, {
    refresh: () => void;
    setFilter: (handle: any, values: any) => void;
    parameters: import("vue").ComputedRef<{
        [x: string]: any;
    }>;
}, {}, {}, {}, import("vue").ComponentOptionsMixin, import("vue").ComponentOptionsMixin, {
    reordered: (...args: any[]) => void;
    "update:columns": (...args: any[]) => void;
    "update:sortColumn": (...args: any[]) => void;
    "update:sortDirection": (...args: any[]) => void;
    "update:selections": (...args: any[]) => void;
    "update:searchQuery": (...args: any[]) => void;
    requestCompleted: (...args: any[]) => void;
    refreshing: (...args: any[]) => void;
}, string, import("vue").PublicProps, Readonly<import("vue").ExtractPropTypes<{
    /** The URL from which to retrieve results. Either use this or `items`. */
    url: {
        type: StringConstructor;
    };
    /** Array of items to display in the listing. When provided, sorting and filtering is done client-side. Either use this or `url`. */
    items: {
        type: ArrayConstructor;
    };
    /** When `true`, allows users to save and load column/filter presets. */
    allowPresets: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, bulk actions are available when items are selected. */
    allowBulkActions: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The URL from which to retrieve actions. */
    actionUrl: {
        type: StringConstructor;
    };
    /** Extra data to pass to the server when using actions. */
    actionContext: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** When `true`, enables the action dropdown while reordering is enabled. */
    allowActionsWhileReordering: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, adds drag handles to the rows. */
    reorderable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Any preferences (preferred columns, etc) will be saved nested under this. */
    preferencesPrefix: {
        type: StringConstructor;
    };
    /** The columns to display. Can be array of strings or column definitions. */
    columns: {
        type: ArrayConstructor;
    };
    /** When `true`, users can show/hide columns. */
    allowCustomizingColumns: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Defines the sort column. */
    sortColumn: {
        type: StringConstructor;
        default: string;
    };
    /** Defines the sort direction. Defaults to `asc` for most fields, `desc` for dates. <br><br> Options: `asc`, `desc` */
    sortDirection: {
        type: StringConstructor;
        default: string;
    };
    /** When `true`, columns can be sorted by clicking on headers. */
    sortable: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Array of checked item IDs. */
    selections: {
        type: ArrayConstructor;
    };
    /** Maximum number of items that can be selected. */
    maxSelections: {
        type: NumberConstructor;
        default: number;
    };
    /** When `true`, adds the parameters to the current URL. */
    pushQuery: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** Extra data to send to the AJAX URL. */
    additionalParameters: {
        type: ObjectConstructor;
        default: () => {};
    };
    /** When `true`, displays a search input for filtering items. */
    allowSearch: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** The search query value. */
    searchQuery: {
        type: StringConstructor;
        default: null;
    };
    /** Array of filter definitions. You can get this by doing `Scope::filters($name, $context)` */
    filters: {
        type: ArrayConstructor;
        default: () => never[];
    };
    /** A function that returns array of filter values to be activated when reordering is enabled. */
    filtersForReordering: {
        type: FunctionConstructor;
        default: null;
    };
    /** Number of items to display per page. */
    perPage: {
        type: NumberConstructor;
        default: () => any;
    };
    /** When `true`, shows the totals in the paginator. e.g. "1-5 of 10" */
    showPaginationTotals: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows the page links. e.g. 1,2,3,4. With this disabled you'll just get the prev/next arrows. */
    showPaginationPageLinks: {
        type: BooleanConstructor;
        default: boolean;
    };
    /** When `true`, shows the per page dropdown. */
    showPaginationPerPageSelector: {
        type: BooleanConstructor;
        default: boolean;
    };
}>> & Readonly<{
    onReordered?: ((...args: any[]) => any) | undefined;
    "onUpdate:columns"?: ((...args: any[]) => any) | undefined;
    "onUpdate:sortColumn"?: ((...args: any[]) => any) | undefined;
    "onUpdate:sortDirection"?: ((...args: any[]) => any) | undefined;
    "onUpdate:selections"?: ((...args: any[]) => any) | undefined;
    "onUpdate:searchQuery"?: ((...args: any[]) => any) | undefined;
    onRequestCompleted?: ((...args: any[]) => any) | undefined;
    onRefreshing?: ((...args: any[]) => any) | undefined;
}>, {
    filters: unknown[];
    sortable: boolean;
    maxSelections: number;
    searchQuery: string;
    perPage: number;
    actionContext: Record<string, any>;
    reorderable: boolean;
    sortColumn: string;
    sortDirection: string;
    allowActionsWhileReordering: boolean;
    showPaginationTotals: boolean;
    showPaginationPageLinks: boolean;
    showPaginationPerPageSelector: boolean;
    allowPresets: boolean;
    allowBulkActions: boolean;
    allowCustomizingColumns: boolean;
    pushQuery: boolean;
    additionalParameters: Record<string, any>;
    allowSearch: boolean;
    filtersForReordering: Function;
}, {}, {}, {}, string, import("vue").ComponentProvideOptions, true, {}, any>, {
    initializing?: (props: {}) => any;
} & {
    default?: (props: {
        items: unknown[] | undefined;
        isColumnVisible: (column: any) => unknown;
        loading: boolean;
    }) => any;
} & {
    'prepended-row-actions'?: (props: {
        row: Record<string, any>;
    }) => any;
}>;
type __VLS_WithSlots<T, S> = T & (new () => {
    $slots: S;
});
