import { expect, test, vi } from 'vitest';
import PublishForm from '@/components/users/PublishForm.vue';

function emitSaved(closeAfterSave) {
    const component = {
        closeAfterSave,
        $emit: vi.fn(),
        $nextTick: (callback) => callback(),
    };

    PublishForm.methods.emitSaved.call(component, 'response');

    return component;
}

test('saving emits saved without closing', () => {
    const component = emitSaved(false);

    expect(component.$emit).toHaveBeenCalledWith('saved', 'response');
    expect(component.$emit).not.toHaveBeenCalledWith('close');
});

test('saving and closing emits saved and then close', () => {
    const component = emitSaved(true);

    expect(component.$emit.mock.calls).toEqual([['saved', 'response'], ['close']]);
    expect(component.closeAfterSave).toBe(false);
});
