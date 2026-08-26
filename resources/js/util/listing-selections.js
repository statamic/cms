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
    maxSelections = Infinity,
}) {
    if (!hasUrl || allMatchingSelected || !pageFullySelected) return false;
    if (!total || total <= pageSize) return false;
    if (maxSelections !== Infinity && maxSelections < total) return false;

    return true;
}
