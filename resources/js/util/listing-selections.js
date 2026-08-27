/**
 * Helpers for page-aware listing selection (including select-all-matching).
 */

export function pageItemIds(items) {
    return (items || []).map((item) => item.id);
}

export function selectedOnPage(items, selections) {
    const selected = new Set(selections || []);

    return pageItemIds(items).filter((id) => selected.has(id));
}

export function isPageFullySelected(items, selections) {
    const ids = pageItemIds(items);

    return ids.length > 0 && ids.every((id) => (selections || []).includes(id));
}

export function isPagePartiallySelected(items, selections) {
    const count = selectedOnPage(items, selections).length;
    const total = pageItemIds(items).length;

    return count > 0 && count < total;
}

export function unionPageSelections(selections, pageIds, maxSelections = Infinity) {
    const next = [...(selections || [])];

    for (const id of pageIds || []) {
        if (next.length >= maxSelections) break;
        if (!next.includes(id)) next.push(id);
    }

    return next;
}

export function removePageSelections(selections, pageIds) {
    const remove = new Set(pageIds || []);

    return (selections || []).filter((id) => !remove.has(id));
}

export function canSelectAllMatching({
    hasUrl,
    total,
    pageSize,
    pageFullySelected,
    allMatchingSelected,
    selectedCount = 0,
    maxSelections = Infinity,
}) {
    if (!hasUrl || allMatchingSelected || !pageFullySelected) return false;
    if (!total || total <= pageSize) return false;
    if (selectedCount >= total) return false;
    if (maxSelections !== Infinity && maxSelections < total) return false;

    return true;
}

export function isRequestCanceled(error) {
    return (
        error?.code === 'ERR_CANCELED' ||
        error?.name === 'CanceledError' ||
        error?.name === 'AbortError' ||
        error?.__CANCEL__ === true
    );
}

/**
 * Fetch every matching listing ID by paging through results.
 * Listing APIs clamp perPage (cpPerPage ceiling), so a single request cannot load all IDs.
 */
export async function fetchAllMatchingIds({
    get,
    url,
    parameters = {},
    total,
    pageSize,
    signal,
    maxSelections = Infinity,
}) {
    const perPage = Math.max(1, pageSize || 100);
    const ids = [];
    const seen = new Set();
    let page = 1;
    let lastPage = Math.max(1, Math.ceil(total / perPage));

    while (page <= lastPage) {
        if (signal?.aborted) {
            const error = new Error('canceled');
            error.code = 'ERR_CANCELED';
            error.name = 'CanceledError';
            throw error;
        }

        const response = await get(url, {
            params: {
                ...parameters,
                page,
                perPage,
            },
            signal,
        });

        const pageItems = Object.values(response?.data?.data || {});

        for (const item of pageItems) {
            const id = item.id;
            if (seen.has(id)) continue;
            seen.add(id);
            ids.push(id);

            if (maxSelections !== Infinity && ids.length >= maxSelections) {
                return ids;
            }
        }

        const responseLastPage = response?.data?.meta?.last_page;
        if (responseLastPage) {
            lastPage = responseLastPage;
        }

        if (pageItems.length === 0) break;

        page++;
    }

    return ids;
}
