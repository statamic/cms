import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import AssetBrowserMixin from '@/components/assets/Browser/AssetBrowserMixin.js';

const movable = (id) => ({ id, path: `photos/${id}.jpg`, actions: [{ handle: 'move_asset' }] });

const browser = (props) =>
    mount(
        {
            mixins: [AssetBrowserMixin],
            props: { assets: Array },
            template: '<div />',
        },
        { props: { folders: [], ...props } },
    ).vm;

test('dragging a selected asset brings the rest of the selection', () => {
    const vm = browser({ assets: ['a', 'b', 'c'].map(movable), selectedAssets: ['a', 'b'] });

    vm.draggingAsset = 'a';

    expect(vm.draggedAssetIds).toEqual(['a', 'b']);
});

test('dragging an unselected asset only brings that asset', () => {
    const vm = browser({ assets: ['a', 'b', 'c'].map(movable), selectedAssets: ['a', 'b'] });

    vm.draggingAsset = 'c';

    expect(vm.draggedAssetIds).toEqual(['c']);
});

test('selected assets that are still moving are left behind', () => {
    const vm = browser({ assets: ['a', 'b', 'c', 'd'].map(movable), selectedAssets: ['a', 'b', 'c', 'd'], moving: ['a', 'b'] });

    vm.draggingAsset = 'd';

    expect(vm.draggedAssetIds).toEqual(['c', 'd']);
});

test('selected assets that cannot be moved are left behind', () => {
    const vm = browser({ assets: [movable('a'), { ...movable('b'), actions: [] }], selectedAssets: ['a', 'b'] });

    vm.draggingAsset = 'a';

    expect(vm.draggedAssetIds).toEqual(['a']);
});

test('a drag is not movable once the dragged asset has gone', () => {
    const vm = browser({ assets: [movable('a')] });

    vm.draggingAsset = 'gone';

    expect(vm.draggingMovable).toBe(false);
});
