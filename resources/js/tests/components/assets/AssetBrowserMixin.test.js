import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import { h } from 'vue';
import AssetBrowserMixin from '@/components/assets/Browser/AssetBrowserMixin';

const moveAsset = { handle: 'move_asset', context: { container: 'assets' } };
const moveFolder = { handle: 'move_asset_folder', context: { container: 'assets' } };

const asset = (path, { movable = true } = {}) => ({
    id: `assets::${path}`,
    path,
    actions: movable ? [moveAsset] : [],
});

const folder = (path, { movable = true } = {}) => ({
    path,
    actions: movable ? [moveFolder] : [],
});

function browser(props = {}) {
    return mount(
        {
            mixins: [AssetBrowserMixin],
            props: { assets: Array },
            render: () => h('div'),
        },
        {
            props: {
                actionUrl: '/asset-actions',
                folderActionUrl: '/folder-actions',
                assets: [],
                folders: [],
                ...props,
            },
        },
    );
}

test('dragging an unselected asset only moves that asset', () => {
    const wrapper = browser({
        assets: [asset('a.jpg'), asset('b.jpg')],
        selectedAssets: ['assets::b.jpg'],
    });

    wrapper.vm.draggingAsset = 'assets::a.jpg';
    wrapper.vm.handleFolderDrop({ path: 'photos' });

    expect(wrapper.emitted('move-items')).toEqual([
        [{ url: '/asset-actions', action: moveAsset, items: ['assets::a.jpg'], destination: 'photos' }],
    ]);
});

test('dragging a selected asset moves the rest of the movable selection with it', () => {
    const wrapper = browser({
        assets: [asset('a.jpg'), asset('b.jpg'), asset('locked.jpg', { movable: false }), asset('unselected.jpg')],
        selectedAssets: ['assets::a.jpg', 'assets::b.jpg', 'assets::locked.jpg'],
    });

    wrapper.vm.draggingAsset = 'assets::a.jpg';

    expect(wrapper.vm.isDragging('assets::b.jpg')).toBe(true);
    expect(wrapper.vm.isDragging('assets::unselected.jpg')).toBe(false);

    wrapper.vm.handleFolderDrop({ path: 'photos' });

    expect(wrapper.emitted('move-items')[0][0].items).toEqual(['assets::a.jpg', 'assets::b.jpg']);
});

test('assets that are already in the destination are skipped', () => {
    const wrapper = browser({
        assets: [asset('photos/a.jpg'), asset('b.jpg')],
        selectedAssets: ['assets::photos/a.jpg', 'assets::b.jpg'],
    });

    wrapper.vm.draggingAsset = 'assets::photos/a.jpg';
    wrapper.vm.handleFolderDrop({ path: 'photos' });

    expect(wrapper.emitted('move-items')[0][0].items).toEqual(['assets::b.jpg']);
});

test('nothing is moved when every dragged asset is already in the destination', () => {
    const wrapper = browser({ assets: [asset('a.jpg')] });

    wrapper.vm.draggingAsset = 'assets::a.jpg';
    wrapper.vm.handleFolderDrop({ path: '/' });

    expect(wrapper.emitted('move-items')).toBeUndefined();
});

test('an asset that cannot be moved is not dropped', () => {
    const wrapper = browser({ assets: [asset('a.jpg', { movable: false })] });

    wrapper.vm.draggingAsset = 'assets::a.jpg';
    wrapper.vm.handleFolderDrop({ path: 'photos' });

    expect(wrapper.emitted('move-items')).toBeUndefined();
});

test('dragging a folder moves it', () => {
    const wrapper = browser({ folders: [folder('photos'), folder('docs')] });

    wrapper.vm.draggingFolder = 'photos';
    wrapper.vm.handleFolderDrop({ path: 'docs' });

    expect(wrapper.emitted('move-items')).toEqual([
        [{ url: '/folder-actions', action: moveFolder, items: ['photos'], destination: 'docs' }],
    ]);
});

test('a folder is not moved into the folder it is already in', () => {
    const wrapper = browser({ folders: [folder('photos/2024')] });

    wrapper.vm.draggingFolder = 'photos/2024';
    wrapper.vm.handleFolderDrop({ path: 'photos' });

    expect(wrapper.emitted('move-items')).toBeUndefined();
});

test('a folder only accepts drags of other movable items', () => {
    const wrapper = browser({ folders: [folder('photos'), folder('docs')] });
    const dragOver = () => ({ preventDefault: vi.fn() });

    wrapper.vm.draggingFolder = 'photos';

    const ontoItself = dragOver();
    wrapper.vm.folderDragOver(ontoItself, folder('photos'));
    expect(ontoItself.preventDefault).not.toHaveBeenCalled();
    expect(wrapper.vm.dragOverFolder).toBeNull();

    const ontoAnother = dragOver();
    wrapper.vm.folderDragOver(ontoAnother, folder('docs'));
    expect(ontoAnother.preventDefault).toHaveBeenCalled();
    expect(wrapper.vm.dragOverFolder).toBe('docs');
});

test('dropping resets the drag state', () => {
    const wrapper = browser({ assets: [asset('a.jpg')], folders: [folder('photos')] });

    wrapper.vm.draggingAsset = 'assets::a.jpg';
    wrapper.vm.dragOverFolder = 'photos';
    wrapper.vm.handleFolderDrop({ path: 'photos' });

    expect(wrapper.vm.draggingAsset).toBeNull();
    expect(wrapper.vm.draggingFolder).toBeNull();
    expect(wrapper.vm.dragOverFolder).toBeNull();
});

test('removed folders are left out of the visible folders', () => {
    const wrapper = browser({ folders: [folder('photos'), folder('docs')], removed: ['photos'] });

    expect(wrapper.vm.visibleFolders.map((folder) => folder.path)).toEqual(['docs']);
});

test('the drop for a folder is found by its path', () => {
    const drop = { id: 1, path: 'photos', files: [], closing: false };
    const wrapper = browser({ drops: [drop] });

    expect(wrapper.vm.dropFor(folder('photos'))).toEqual(drop);
    expect(wrapper.vm.dropFor(folder('docs'))).toBeNull();
});
