import { test, expect, vi, beforeEach, afterEach } from 'vitest';
import {
    isInTooltipHoverRegion,
    tooltipGapRect,
    useTooltip,
} from '../composables/tooltip';

const trigger = { left: 100, right: 200, top: 200, bottom: 220 };
const popperAbove = { left: 110, right: 190, top: 160, bottom: 190 };
const popperBelow = { left: 110, right: 190, top: 230, bottom: 260 };
const popperLeft = { left: 20, right: 90, top: 180, bottom: 240 };
const popperRight = { left: 210, right: 280, top: 180, bottom: 240 };

let mounted = [];

function box(rect) {
    const el = document.createElement('div');
    el.getBoundingClientRect = () => ({
        ...rect,
        width: rect.right - rect.left,
        height: rect.bottom - rect.top,
        x: rect.left,
        y: rect.top,
        toJSON() {},
    });
    el.hovered = false;
    el.matches = (selector) => selector === ':hover' && el.hovered;
    el.closest = (selector) => (selector === '.v-popper__popper' ? el : null);
    document.body.appendChild(el);
    mounted.push(el);

    return el;
}

function resetTooltip() {
    const { hide, targetEl, dismissFor } = useTooltip();
    if (targetEl.value) dismissFor(targetEl.value);
    hide();
}

beforeEach(() => {
    vi.useFakeTimers();
    resetTooltip();
});

afterEach(() => {
    resetTooltip();
    mounted.forEach((el) => el.remove());
    mounted = [];
    vi.useRealTimers();
});

test('it treats the trigger and popper as inside the hover region', () => {
    expect(isInTooltipHoverRegion(150, 210, trigger, popperAbove)).toBe(true);
    expect(isInTooltipHoverRegion(150, 175, trigger, popperAbove)).toBe(true);
});

test('it treats the gap between a stacked trigger and popper as inside', () => {
    expect(isInTooltipHoverRegion(150, 195, trigger, popperAbove)).toBe(true);
    expect(isInTooltipHoverRegion(150, 225, trigger, popperBelow)).toBe(true);
});

test('it treats the gap between a side-placed trigger and popper as inside', () => {
    expect(isInTooltipHoverRegion(95, 210, trigger, popperLeft)).toBe(true);
    expect(isInTooltipHoverRegion(205, 210, trigger, popperRight)).toBe(true);
});

test('it treats a slight sideways drift in the gap as inside', () => {
    expect(isInTooltipHoverRegion(208, 195, trigger, popperAbove)).toBe(true);
    expect(isInTooltipHoverRegion(92, 195, trigger, popperAbove)).toBe(true);
});

test('it treats points outside the hover region as outside', () => {
    expect(isInTooltipHoverRegion(50, 50, trigger, popperAbove)).toBe(false);
    expect(isInTooltipHoverRegion(50, 195, trigger, popperAbove)).toBe(false);
});

test('it keeps the hover region open while the popper is unmeasured', () => {
    expect(isInTooltipHoverRegion(50, 50, trigger, null)).toBe(true);
    expect(isInTooltipHoverRegion(150, 195, trigger, null)).toBe(true);
});

test('tooltipGapRect returns null when the rects overlap', () => {
    const overlapping = { left: 120, right: 180, top: 210, bottom: 250 };

    expect(tooltipGapRect(trigger, overlapping)).toBeNull();
});

test('an interactive tooltip stays open while the pointer crosses the gap', () => {
    const { show, hide, isVisible, registerContentEl } = useTooltip();
    const triggerEl = box(trigger);
    const popperEl = box(popperAbove);
    triggerEl.hovered = true;

    show(triggerEl, { content: 'title', copyable: true });
    vi.advanceTimersByTime(200);
    registerContentEl(popperEl);

    expect(isVisible.value).toBe(true);

    triggerEl.hovered = false;
    document.dispatchEvent(new MouseEvent('mousemove', { clientX: 150, clientY: 195, bubbles: true }));
    hide();

    expect(isVisible.value).toBe(true);
});

test('an interactive tooltip dismisses when the pointer leaves the hover region', () => {
    const { show, isVisible, registerContentEl } = useTooltip();
    const triggerEl = box(trigger);
    const popperEl = box(popperAbove);
    triggerEl.hovered = true;

    show(triggerEl, { content: 'title', copyable: true });
    vi.advanceTimersByTime(200);
    registerContentEl(popperEl);

    expect(isVisible.value).toBe(true);

    triggerEl.hovered = false;
    document.dispatchEvent(new MouseEvent('mousemove', { clientX: 50, clientY: 50, bubbles: true }));

    expect(isVisible.value).toBe(false);
});

test('an interactive tooltip stays open while crossing the gap before the popper mounts', () => {
    const { show, hide, isVisible, registerContentEl } = useTooltip();
    const triggerEl = box(trigger);
    const popperEl = box(popperAbove);

    show(triggerEl, { content: 'title', copyable: true });
    vi.advanceTimersByTime(200);

    document.dispatchEvent(new MouseEvent('mousemove', { clientX: 150, clientY: 195, bubbles: true }));
    hide();

    expect(isVisible.value).toBe(true);

    registerContentEl(popperEl);

    expect(isVisible.value).toBe(true);
});

test('registering the popper dismisses when the pointer is unknown and nothing is hovered', () => {
    const { show, isVisible, registerContentEl } = useTooltip();
    const triggerEl = box(trigger);
    const popperEl = box(popperAbove);

    show(triggerEl, { content: 'title', copyable: true });
    vi.advanceTimersByTime(200);
    registerContentEl(popperEl);

    expect(isVisible.value).toBe(false);
});

test('hide dismisses an interactive tooltip when the pointer is unknown and the trigger is no longer hovered', () => {
    const { show, hide, isVisible, registerContentEl } = useTooltip();
    const triggerEl = box(trigger);
    const popperEl = box(popperAbove);
    triggerEl.hovered = true;

    show(triggerEl, { content: 'title', copyable: true });
    vi.advanceTimersByTime(200);
    registerContentEl(popperEl);

    expect(isVisible.value).toBe(true);

    triggerEl.hovered = false;
    hide();

    expect(isVisible.value).toBe(false);
});

test('a plain tooltip still dismisses immediately on hide', () => {
    const { show, hide, isVisible } = useTooltip();
    const triggerEl = box(trigger);

    show(triggerEl, 'Hello');
    vi.advanceTimersByTime(200);

    expect(isVisible.value).toBe(true);

    hide();

    expect(isVisible.value).toBe(false);
});
