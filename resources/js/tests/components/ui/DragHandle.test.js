import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import DragHandle from '@/components/ui/DragHandle.vue';

test('default handle keeps the drag-to-reorder label and does not emit on space', async () => {
    const wrapper = mount(DragHandle);
    const button = wrapper.get('button');

    expect(button.attributes('aria-label')).toBe('Drag to reorder');

    await button.trigger('keydown', { key: ' ' });
    expect(wrapper.emitted('keyboard-reorder')).toBeUndefined();
});

test('a consumer aria-label wins over the default label', () => {
    const wrapper = mount(DragHandle, {
        attrs: { 'aria-label': 'Move column' },
    });

    expect(wrapper.get('button').attributes('aria-label')).toBe('Move column');
});

test('keyboard-reorder opt-in emits on space and click-like pointerup', async () => {
    const wrapper = mount(DragHandle, {
        props: { keyboardReorder: true },
    });
    const button = wrapper.get('button');

    expect(button.attributes('aria-label')).toBe('messages.keyboard_item_reorder_handle');

    await button.trigger('keydown', { key: ' ' });
    expect(wrapper.emitted('keyboard-reorder')).toHaveLength(1);

    await button.trigger('pointerdown', { button: 0, pointerType: 'mouse', pageX: 10, pageY: 10 });
    window.dispatchEvent(new Event('pointerup', { bubbles: true }));

    expect(wrapper.emitted('keyboard-reorder')).toHaveLength(2);
});

test('touch pointerdown does not enter keyboard reorder', async () => {
    const wrapper = mount(DragHandle, {
        props: { keyboardReorder: true },
    });

    await wrapper.get('button').trigger('pointerdown', {
        button: 0,
        pointerType: 'touch',
        pageX: 10,
        pageY: 10,
    });
    window.dispatchEvent(new Event('pointerup', { bubbles: true }));

    expect(wrapper.emitted('keyboard-reorder')).toBeUndefined();
});
