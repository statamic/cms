import { expect, test, vi } from 'vitest';
import Editor from '@/components/assets/Editor/Editor.vue';

function keydown(key, target, modifier) {
    const component = {
        navigateToPreviousAsset: vi.fn(),
        navigateToNextAsset: vi.fn(),
        isEditingText: Editor.methods.isEditingText,
    };

    Editor.methods.keydown.call(component, { key, ctrlKey: false, metaKey: false, target, [modifier]: true });

    return component;
}

test.each([['ctrlKey'], ['metaKey']])('%s + arrow keys navigate between assets', (modifier) => {
    const target = document.createElement('div');

    expect(keydown('ArrowLeft', target, modifier).navigateToPreviousAsset).toHaveBeenCalled();
    expect(keydown('ArrowRight', target, modifier).navigateToNextAsset).toHaveBeenCalled();
});

test.each([
    ['ctrlKey', 'input'],
    ['ctrlKey', 'textarea'],
    ['ctrlKey', 'select'],
    ['metaKey', 'input'],
    ['metaKey', 'textarea'],
    ['metaKey', 'select'],
])('%s + arrow keys do not navigate between assets when focused on a %s', (modifier, tag) => {
    const target = document.createElement(tag);

    expect(keydown('ArrowLeft', target, modifier).navigateToPreviousAsset).not.toHaveBeenCalled();
    expect(keydown('ArrowRight', target, modifier).navigateToNextAsset).not.toHaveBeenCalled();
});

test.each([['ctrlKey'], ['metaKey']])(
    '%s + arrow keys do not navigate between assets when focused on a contenteditable element',
    (modifier) => {
        const target = document.createElement('div');

        // jsdom doesn't implement isContentEditable.
        Object.defineProperty(target, 'isContentEditable', { value: true });

        expect(keydown('ArrowLeft', target, modifier).navigateToPreviousAsset).not.toHaveBeenCalled();
        expect(keydown('ArrowRight', target, modifier).navigateToNextAsset).not.toHaveBeenCalled();
    },
);
