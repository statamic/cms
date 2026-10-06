import { mount } from '@vue/test-utils';
import { defineComponent, nextTick, ref } from 'vue';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { useKeyboardItemReorder } from '@/composables/keyboard-item-reorder.js';

function mountReorder({
    index = 1,
    total = 3,
    onMove = vi.fn(),
    template = `
        <div data-moving="true" data-reorder-focus tabindex="-1">
            <div data-reorder-focus tabindex="-1">nested</div>
            <button ref="rootEl" type="button">sentinel</button>
        </div>
    `,
} = {}) {
    const indexRef = ref(index);
    const totalRef = ref(total);
    let api;

    const Comp = defineComponent({
        setup() {
            api = useKeyboardItemReorder({
                index: indexRef,
                total: totalRef,
                onMove,
            });

            return { ...api, indexRef };
        },
        template,
    });

    const wrapper = mount(Comp);

    return { wrapper, api, indexRef, totalRef, onMove };
}

beforeEach(() => {
    vi.useFakeTimers();
});

afterEach(() => {
    vi.useRealTimers();
});

test('it starts move mode and announces instructions', async () => {
    const { api } = mountReorder();

    api.startMoving('start');
    vi.advanceTimersByTime(50);
    await nextTick();

    expect(api.moving.value).toBe(true);
    expect(api.moveOrigin.value).toBe('start');
    expect(api.status.value).toBe('messages.keyboard_item_reorder_instructions');
});

test('it does not start when there is only one item', async () => {
    const { api } = mountReorder({ total: 1 });

    api.startMoving('start');
    vi.advanceTimersByTime(50);
    await nextTick();

    expect(api.moving.value).toBe(false);
});

test('arrow keys move and announce at edges', async () => {
    const { api, onMove } = mountReorder({ index: 0, total: 3 });

    api.startMoving('end');
    vi.advanceTimersByTime(50);
    await nextTick();

    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowUp', bubbles: true }));
    expect(onMove).not.toHaveBeenCalled();
    await nextTick();
    expect(api.status.value).toBe('messages.keyboard_item_reorder_position');

    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowDown', bubbles: true }));
    expect(onMove).toHaveBeenCalledWith(0, 1);
});

test('escape restores focus to the moving item when it is the reorder focus target', async () => {
    const { wrapper, api } = mountReorder();
    const row = wrapper.find('[data-moving]').element;
    const nested = wrapper.find('[data-moving] [data-reorder-focus]').element;
    const rowFocus = vi.spyOn(row, 'focus');
    const nestedFocus = vi.spyOn(nested, 'focus');

    api.startMoving('start');
    vi.advanceTimersByTime(50);
    await nextTick();

    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
    await nextTick();

    expect(api.moving.value).toBe(false);
    expect(rowFocus).toHaveBeenCalled();
    expect(nestedFocus).not.toHaveBeenCalled();
});

test('starting a second session stops the first', async () => {
    const first = mountReorder();
    const second = mountReorder();

    first.api.startMoving('start');
    vi.advanceTimersByTime(50);
    await nextTick();
    expect(first.api.moving.value).toBe(true);

    second.api.startMoving('end');
    vi.advanceTimersByTime(50);
    await nextTick();

    expect(first.api.moving.value).toBe(false);
    expect(second.api.moving.value).toBe(true);

    first.wrapper.unmount();
    second.wrapper.unmount();
});

test('tabbing away ends move mode without trapping keys', async () => {
    const { api } = mountReorder();

    api.startMoving('start');
    vi.advanceTimersByTime(50);
    await nextTick();

    window.dispatchEvent(new KeyboardEvent('keydown', { key: 'Tab', bubbles: true }));
    expect(api.moving.value).toBe(false);
});
