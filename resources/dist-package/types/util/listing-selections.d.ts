/**
 * Helpers for page-aware listing selection (including select-all-matching).
 */
export function pageItemIds(items: any): any;
export function selectedOnPage(items: any, selections: any): any;
export function isPageFullySelected(items: any, selections: any): any;
export function isPagePartiallySelected(items: any, selections: any): boolean;
export function unionPageSelections(selections: any, pageIds: any, maxSelections?: number): any[];
export function removePageSelections(selections: any, pageIds: any): any;
export function replaceSelections(selections: any, ids: any): void;
export function canSelectAllMatching({ hasUrl, total, pageSize, pageFullySelected, allMatchingSelected, selectedCount, maxSelections, selectAllLimit, }: {
    hasUrl: any;
    total: any;
    pageSize: any;
    pageFullySelected: any;
    allMatchingSelected: any;
    selectedCount?: number | undefined;
    maxSelections?: number | undefined;
    selectAllLimit?: number | undefined;
}): boolean;
/**
 * Resolve the CP select-all ceiling. `null` means no limit; invalid values use the fallback.
 */
export function resolveSelectAllLimit(value: any, fallback?: number): number;
export function isRequestCanceled(error: any): boolean;
/**
 * Fetch every matching listing ID by paging through results.
 * Listing APIs clamp perPage (cpPerPage ceiling), so a single request cannot load all IDs.
 */
export function fetchAllMatchingIds({ get, url, parameters, total, pageSize, signal, maxSelections, selectAllLimit, }: {
    get: any;
    url: any;
    parameters?: {} | undefined;
    total: any;
    pageSize: any;
    signal: any;
    maxSelections?: number | undefined;
    selectAllLimit?: number | undefined;
}): Promise<any[]>;
