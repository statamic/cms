import { expect, test, vi } from 'vitest';
import Editor from '@/components/assets/Editor/Editor.vue';

function keydown(key, target) {
    const component = {
        navigateToPreviousAsset: vi.fn(),
        navigateToNextAsset: vi.fn(),
        isEditingText: Editor.methods.isEditingText,
    };

    Editor.methods.keydown.call(component, { key, ctrlKey: true, metaKey: false, target });

    return component;
}

test('ctrl + arrow keys navigate between assets', () => {
    const target = document.createElement('div');

    expect(keydown('ArrowLeft', target).navigateToPreviousAsset).toHaveBeenCalled();
    expect(keydown('ArrowRight', target).navigateToNextAsset).toHaveBeenCalled();
});

test.each([['input'], ['textarea'], ['select']])(
    'ctrl + arrow keys do not navigate between assets when focused on a %s',
    (tag) => {
        const target = document.createElement(tag);

        expect(keydown('ArrowLeft', target).navigateToPreviousAsset).not.toHaveBeenCalled();
        expect(keydown('ArrowRight', target).navigateToNextAsset).not.toHaveBeenCalled();
    },
);

test('ctrl + arrow keys do not navigate between assets when focused on a contenteditable element', () => {
    const target = document.createElement('div');

    // jsdom doesn't implement isContentEditable.
    Object.defineProperty(target, 'isContentEditable', { value: true });

    expect(keydown('ArrowLeft', target).navigateToPreviousAsset).not.toHaveBeenCalled();
    expect(keydown('ArrowRight', target).navigateToNextAsset).not.toHaveBeenCalled();
});
