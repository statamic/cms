import { expect, test, vi } from 'vitest';
import Editor from '@/components/assets/Editor/Editor.vue';
import Asset from '@/components/fieldtypes/assets/Asset';

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

test('keeps crop copies in the asset field', () => {
    const editor = { redirectAfterCrop: false, $emit: vi.fn() };
    const asset = { editingId: 'assets::source.png', $emit: vi.fn(), closeEditor: vi.fn() };

    Editor.methods.handleCropCreated.call(editor, 'assets::crop.png');
    Asset.methods.assetCreated.call(asset, 'assets::crop.png');

    expect(editor.$emit).toHaveBeenCalledWith('created', 'assets::crop.png');
    expect(asset.$emit).toHaveBeenCalledWith('id-changed', 'assets::source.png', 'assets::crop.png');
    expect(asset.closeEditor).toHaveBeenCalled();
});
