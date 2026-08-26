import { expect, test } from 'vitest';
import {
    canSelectAllMatching,
    isPageFullySelected,
    isPagePartiallySelected,
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
