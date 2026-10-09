import { expect, test } from 'vitest';
import arrayMove from '@/components/sortable/arrayMove.js';

test('it moves an item forward', () => {
    expect(arrayMove(['a', 'b', 'c'], 0, 2)).toEqual(['b', 'c', 'a']);
});

test('it moves an item backward', () => {
    expect(arrayMove(['a', 'b', 'c'], 2, 0)).toEqual(['c', 'a', 'b']);
});

test('it leaves the array unchanged when indexes match', () => {
    expect(arrayMove(['a', 'b', 'c'], 1, 1)).toEqual(['a', 'b', 'c']);
});
