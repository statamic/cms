import { describe, expect, it } from 'vitest';
import {
    addResourceIndexItems,
    isResourceIndexFallbackReorder,
    moveResourceIndexItem,
    removeResourceIndexItem,
    unassignedResourceIndexItems,
} from '@/components/resource-indexes/organize-groups.js';

const fallbackGroupId = '__other';

describe('resource index organization', () => {
    it('places items without a group membership in Other', () => {
        const items = [{ id: 'one' }, { id: 'two' }, { id: 'three' }];
        const groups = [
            { id: 'primary', items: ['one'] },
            { id: 'secondary', items: ['one', 'two'] },
        ];

        expect(unassignedResourceIndexItems(items, groups)).toEqual([{ id: 'three' }]);
    });

    it('allows transfers to and from Other but prevents reordering within it', () => {
        expect(isResourceIndexFallbackReorder(fallbackGroupId, fallbackGroupId, fallbackGroupId)).toBe(true);
        expect(isResourceIndexFallbackReorder(fallbackGroupId, 'primary', fallbackGroupId)).toBe(false);
        expect(isResourceIndexFallbackReorder('primary', fallbackGroupId, fallbackGroupId)).toBe(false);
    });

    it('adds selected items without duplicating existing memberships', () => {
        const groups = [{ id: 'primary', items: ['one'] }];

        addResourceIndexItems(groups, 'primary', ['one', 'two', 'three']);

        expect(groups[0].items).toEqual(['one', 'two', 'three']);
    });

    it('reorders items within a group and moves them between groups', () => {
        const groups = [
            { id: 'primary', items: ['one', 'two'] },
            { id: 'secondary', items: ['three'] },
        ];

        moveResourceIndexItem(groups, {
            itemId: 'two',
            oldGroupId: 'primary',
            oldIndex: 1,
            newGroupId: 'primary',
            newIndex: 0,
            fallbackGroupId,
        });
        moveResourceIndexItem(groups, {
            itemId: 'one',
            oldGroupId: 'primary',
            oldIndex: 1,
            newGroupId: 'secondary',
            newIndex: 1,
            fallbackGroupId,
        });

        expect(groups).toEqual([
            { id: 'primary', items: ['two'] },
            { id: 'secondary', items: ['three', 'one'] },
        ]);
    });

    it("preserves an item's other group memberships when it is moved", () => {
        const groups = [
            { id: 'primary', items: ['one'] },
            { id: 'secondary', items: ['one'] },
            { id: 'tertiary', items: [] },
        ];

        moveResourceIndexItem(groups, {
            itemId: 'one',
            oldGroupId: 'primary',
            oldIndex: 0,
            newGroupId: 'tertiary',
            newIndex: 0,
            fallbackGroupId,
        });

        expect(groups).toEqual([
            { id: 'primary', items: [] },
            { id: 'secondary', items: ['one'] },
            { id: 'tertiary', items: ['one'] },
        ]);
    });

    it('does not duplicate membership when an item is moved into a group it already belongs to', () => {
        const groups = [
            { id: 'primary', items: ['one'] },
            { id: 'secondary', items: ['one', 'two'] },
        ];

        moveResourceIndexItem(groups, {
            itemId: 'one',
            oldGroupId: 'primary',
            oldIndex: 0,
            newGroupId: 'secondary',
            newIndex: 1,
            fallbackGroupId,
        });

        expect(groups).toEqual([
            { id: 'primary', items: [] },
            { id: 'secondary', items: ['one', 'two'] },
        ]);
    });

    it('removes an item from only the selected group', () => {
        const groups = [
            { id: 'primary', items: ['one'] },
            { id: 'secondary', items: ['one'] },
        ];

        removeResourceIndexItem(groups, 'primary', 'one');

        expect(groups).toEqual([
            { id: 'primary', items: [] },
            { id: 'secondary', items: ['one'] },
        ]);
    });

    it('removes all memberships when an item is moved to Other', () => {
        const groups = [
            { id: 'primary', items: ['one'] },
            { id: 'secondary', items: ['one'] },
        ];

        moveResourceIndexItem(groups, {
            itemId: 'one',
            oldGroupId: 'primary',
            oldIndex: 0,
            newGroupId: fallbackGroupId,
            newIndex: 0,
            fallbackGroupId,
        });

        expect(groups).toEqual([
            { id: 'primary', items: [] },
            { id: 'secondary', items: [] },
        ]);
    });
});
