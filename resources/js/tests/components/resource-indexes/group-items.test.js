import { describe, expect, it } from 'vitest';
import { groupResourceIndexItems } from '@/components/resource-indexes/group-items.js';

const fallback = { id: '__other', title: 'Other' };
const resourceIndex = (groups = [], hasSavedGroups = false) => ({
    groups,
    hasSavedGroups,
    fallbackGroup: fallback,
});

describe('resource index grouping', () => {
    it('returns all items in one untitled group when no groups are configured', () => {
        const items = [{ id: 'one' }, { id: 'two' }];

        expect(groupResourceIndexItems(items, resourceIndex())).toEqual([
            { id: '__all', title: null, items },
        ]);
    });

    it('places every item in Other when an empty organization has been saved', () => {
        const items = [{ id: 'one' }, { id: 'two' }];

        expect(groupResourceIndexItems(items, resourceIndex([], true))).toEqual([
            { ...fallback, items },
        ]);
    });

    it('renders saved membership and ordering, including shared and unassigned items', () => {
        const one = { id: 'one' };
        const two = { id: 'two' };
        const three = { id: 'three' };
        const groups = [
            { id: 'primary', title: 'Primary', items: ['two', 'one'] },
            { id: 'secondary', title: 'Secondary', items: ['one'] },
        ];

        expect(groupResourceIndexItems([one, two, three], resourceIndex(groups, true))).toEqual([
            { id: 'primary', title: 'Primary', items: [two, one] },
            { id: 'secondary', title: 'Secondary', items: [one] },
            { id: '__other', title: 'Other', items: [three] },
        ]);
    });

    it('uses listing order for default groups until organization is customized', () => {
        const one = { id: 'one' };
        const two = { id: 'two' };
        const groups = [
            { id: 'primary', title: 'Primary', items: ['two', 'one'] },
        ];

        expect(groupResourceIndexItems([one, two], resourceIndex(groups))).toEqual([
            { id: 'primary', title: 'Primary', items: [one, two] },
        ]);
    });

    it('only includes current listing items and omits empty groups', () => {
        const item = { id: 'one' };
        const groups = [
            { id: 'primary', title: 'Primary', items: ['one', 'two'] },
            { id: 'secondary', title: 'Secondary', items: ['two'] },
        ];

        expect(groupResourceIndexItems([item], resourceIndex(groups, true))).toEqual([
            { id: 'primary', title: 'Primary', items: [item] },
        ]);
    });
});
