/** Shared click-vs-drag threshold for SortableList and DragHandle (px). */
export const POINTER_DRAG_THRESHOLD = 5;

/**
 * Match @shopify/draggable: Euclidean distance, drag starts at >= threshold.
 */
export function pointerDistanceExceeded(start, end, threshold = POINTER_DRAG_THRESHOLD) {
    const dx = end.x - start.x;
    const dy = end.y - start.y;

    return Math.sqrt(dx * dx + dy * dy) >= threshold;
}
