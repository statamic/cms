import { expect, test, vi } from 'vitest';
import {
    canSelectAllMatching,
    fetchAllMatchingIds,
    isPageFullySelected,
    isPagePartiallySelected,
    isRequestCanceled,
    pageItemIds,
    removePageSelections,
    selectedOnPage,
    unionPageSelections,
} from '@/util/listing-selections.js';

const items = [{ id: 'a' }, { id: 'b' }, { id: 'c' }];

test('pageItemIds returns row ids', () => {
    expect(pageItemIds(items)).toEqual(['a', 'b', 'c']);
});

test('selectedOnPage returns only ids present on the page', () => {
    expect(selectedOnPage(items, ['a', 'z', 'c'])).toEqual(['a', 'c']);
});

test('isPageFullySelected requires every page row to be selected', () => {
    expect(isPageFullySelected(items, ['a', 'b', 'c'])).toBe(true);
    expect(isPageFullySelected(items, ['a', 'b', 'c', 'd'])).toBe(true);
    expect(isPageFullySelected(items, ['a', 'b'])).toBe(false);
    expect(isPageFullySelected([], ['a'])).toBe(false);
});

test('isPagePartiallySelected when some but not all page rows are selected', () => {
    expect(isPagePartiallySelected(items, ['a'])).toBe(true);
    expect(isPagePartiallySelected(items, ['a', 'b', 'c'])).toBe(false);
    expect(isPagePartiallySelected(items, ['z'])).toBe(false);
});

test('unionPageSelections adds page ids without wiping other pages', () => {
    expect(unionPageSelections(['z'], ['a', 'b'])).toEqual(['z', 'a', 'b']);
    expect(unionPageSelections(['a'], ['a', 'b'])).toEqual(['a', 'b']);
});

test('unionPageSelections respects maxSelections', () => {
    expect(unionPageSelections(['z'], ['a', 'b', 'c'], 2)).toEqual(['z', 'a']);
});

test('removePageSelections removes only page ids', () => {
    expect(removePageSelections(['z', 'a', 'b'], ['a', 'b'])).toEqual(['z']);
});

test('canSelectAllMatching when the page is fully selected and more results exist', () => {
    expect(
        canSelectAllMatching({
            hasUrl: true,
            total: 50,
            pageSize: 10,
            pageFullySelected: true,
            allMatchingSelected: false,
        }),
    ).toBe(true);
});

test('canSelectAllMatching is false when already selecting all matching', () => {
    expect(
        canSelectAllMatching({
            hasUrl: true,
            total: 50,
            pageSize: 10,
            pageFullySelected: true,
            allMatchingSelected: true,
        }),
    ).toBe(false);
});

test('canSelectAllMatching is false when maxSelections cannot cover the total', () => {
    expect(
        canSelectAllMatching({
            hasUrl: true,
            total: 50,
            pageSize: 10,
            pageFullySelected: true,
            allMatchingSelected: false,
            maxSelections: 20,
        }),
    ).toBe(false);
});

test('canSelectAllMatching is false for client-side listings without a url', () => {
    expect(
        canSelectAllMatching({
            hasUrl: false,
            total: 50,
            pageSize: 10,
            pageFullySelected: true,
            allMatchingSelected: false,
        }),
    ).toBe(false);
});

test('isRequestCanceled recognizes common cancel shapes', () => {
    expect(isRequestCanceled({ code: 'ERR_CANCELED' })).toBe(true);
    expect(isRequestCanceled({ name: 'CanceledError' })).toBe(true);
    expect(isRequestCanceled({ name: 'AbortError' })).toBe(true);
    expect(isRequestCanceled({ __CANCEL__: true })).toBe(true);
    expect(isRequestCanceled({ message: 'boom' })).toBe(false);
});

test('fetchAllMatchingIds pages through results under a perPage ceiling', async () => {
    const get = vi.fn(async (url, { params }) => {
        const pages = {
            1: {
                data: [{ id: '1' }, { id: '2' }],
                meta: { last_page: 3, total: 5 },
            },
            2: {
                data: [{ id: '3' }, { id: '4' }],
                meta: { last_page: 3, total: 5 },
            },
            3: {
                data: [{ id: '5' }],
                meta: { last_page: 3, total: 5 },
            },
        };

        return { data: pages[params.page] };
    });

    const ids = await fetchAllMatchingIds({
        get,
        url: '/cp/entries',
        parameters: { search: 'alfa', sort: 'title' },
        total: 5,
        pageSize: 2,
    });

    expect(ids).toEqual(['1', '2', '3', '4', '5']);
    expect(get).toHaveBeenCalledTimes(3);
    expect(get.mock.calls[0][1].params).toMatchObject({
        search: 'alfa',
        sort: 'title',
        page: 1,
        perPage: 2,
    });
    expect(get.mock.calls[2][1].params.page).toBe(3);
});

test('fetchAllMatchingIds respects maxSelections', async () => {
    const get = vi.fn(async (url, { params }) => ({
        data: {
            data: [{ id: `${params.page}-a` }, { id: `${params.page}-b` }],
            meta: { last_page: 5, total: 10 },
        },
    }));

    const ids = await fetchAllMatchingIds({
        get,
        url: '/cp/entries',
        total: 10,
        pageSize: 2,
        maxSelections: 3,
    });

    expect(ids).toEqual(['1-a', '1-b', '2-a']);
    expect(get).toHaveBeenCalledTimes(2);
});
