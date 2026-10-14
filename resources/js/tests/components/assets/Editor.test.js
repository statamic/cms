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

function navigate(method, { dirty = true, saving = false, save = () => Promise.resolve() } = {}) {
    const component = {
        saving,
        publishContainer: 'asset',
        $dirty: { has: () => dirty },
        $emit: vi.fn(),
        save: vi.fn(save),
    };

    Editor.methods[method].call(component);

    return component;
}

test.each([
    ['navigateToPreviousAsset', 'previous'],
    ['navigateToNextAsset', 'next'],
])('%s navigates straight away when there are no changes', (method, event) => {
    const component = navigate(method, { dirty: false });

    expect(component.save).not.toHaveBeenCalled();
    expect(component.$emit).toHaveBeenCalledWith(event);
});

test.each([
    ['navigateToPreviousAsset', 'previous'],
    ['navigateToNextAsset', 'next'],
])('%s waits for the save before navigating', async (method, event) => {
    let resolve;
    const component = navigate(method, { save: () => new Promise((r) => (resolve = r)) });

    expect(component.save).toHaveBeenCalled();
    expect(component.$emit).not.toHaveBeenCalled();

    resolve();
    await vi.waitFor(() => expect(component.$emit).toHaveBeenCalledWith(event));
});

test.each([['navigateToPreviousAsset'], ['navigateToNextAsset']])(
    '%s does not navigate when the save fails',
    async (method) => {
        const component = navigate(method, { save: () => Promise.reject(new Error()) });

        await new Promise((r) => setTimeout(r));

        expect(component.$emit).not.toHaveBeenCalled();
    },
);

test.each([['navigateToPreviousAsset'], ['navigateToNextAsset']])(
    '%s does nothing while a save is in progress',
    (method) => {
        const component = navigate(method, { saving: true });

        expect(component.save).not.toHaveBeenCalled();
        expect(component.$emit).not.toHaveBeenCalled();
    },
);

function saveFocalPoint(options) {
    const component = {
        $emit: vi.fn(),
        $nextTick: (callback) => callback(),
        save: vi.fn(() => Promise.resolve()),
    };

    Editor.methods.saveFocalPoint.call(component, options);

    return component;
}

test('saving the focal point keeps the asset editor open', async () => {
    const component = saveFocalPoint();

    await vi.waitFor(() => expect(component.save).toHaveBeenCalled());
    await new Promise((r) => setTimeout(r));

    expect(component.$emit).not.toHaveBeenCalledWith('closed');
});

test('saving the focal point can close the asset editor', async () => {
    const component = saveFocalPoint({ close: true });

    await vi.waitFor(() => expect(component.$emit).toHaveBeenCalledWith('closed'));
});
