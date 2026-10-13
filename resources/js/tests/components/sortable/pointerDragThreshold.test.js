import { expect, test } from 'vitest';
import {
    POINTER_DRAG_THRESHOLD,
    pointerDistanceExceeded,
} from '@/components/sortable/pointerDragThreshold.js';

test('it matches shopify draggable at the shared threshold', () => {
    expect(POINTER_DRAG_THRESHOLD).toBe(5);
    expect(pointerDistanceExceeded({ x: 0, y: 0 }, { x: 5, y: 0 })).toBe(true);
    expect(pointerDistanceExceeded({ x: 0, y: 0 }, { x: 4, y: 0 })).toBe(false);
});

test('it uses euclidean distance', () => {
    // 3-4-5 triangle: hypotenuse is exactly 5
    expect(pointerDistanceExceeded({ x: 0, y: 0 }, { x: 3, y: 4 })).toBe(true);
    expect(pointerDistanceExceeded({ x: 0, y: 0 }, { x: 3, y: 3 })).toBe(false);
});
