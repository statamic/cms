import { flushPromises, mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import { preferences } from '@api';
import AssetsFieldtype from '@/components/fieldtypes/assets/AssetsFieldtype.vue';
import AssetCompact from '@/components/fieldtypes/assets/AssetCompact.vue';
import AssetFull from '@/components/fieldtypes/assets/AssetFull.vue';
import AssetUpload from '@/components/fieldtypes/assets/AssetUpload.vue';
import Uploader from '@/components/assets/Uploader.vue';
import Upload from '@/components/assets/Upload.vue';
import Uploads from '@/components/assets/Uploads.vue';
import { Button } from '@/components/ui';

// Register the global helper functions (data_get, clone, etc.) onto the window
// like the control panel bootstrap does, so the component can use them.
Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.cp_url = (url) => url;
window.__ = (key) => key;
preferences.initialize({}, {});

const makeField = ({
    axiosPost = vi.fn(() => Promise.resolve({ data: [] })),
    props = {},
    provide = {},
    stubs = {},
} = {}) => {
    return mount(AssetsFieldtype, {
        shallow: true,
        props: {
            handle: 'image',
            value: [],
            config: {},
            meta: {
                container: { id: 'main', can_view: true, can_upload: true },
                data: [],
            },
            ...props,
        },
        global: {
            provide,
            stubs: {
                Uploader: {
                    template: '<div><slot :dragging="false" /></div>',
                },
                ...stubs,
            },
            mocks: {
                $axios: { post: axiosPost },
                $progress: { loading: () => {} },
                $toast: { error: vi.fn() },
            },
        },
    });
};

test('selecting the existing file references the asset the server reported', () => {
    const post = vi.fn(() => Promise.resolve({ data: [] }));
    const field = makeField({ axiosPost: post, props: { config: { folder: 'sub' } } });

    field.vm.uploadSelected({ basename: 'My File.PNG', existingId: 'main::sub/my-file.png' });

    expect(post).toHaveBeenCalledWith('assets-fieldtype', { assets: ['main::sub/my-file.png'] });
});

test('loading assets for a new value does not emit an update when the ids are unchanged', async () => {
    const post = vi.fn(() => Promise.resolve({ data: [{ id: 'main::b.jpg' }] }));
    const field = makeField({
        axiosPost: post,
        props: {
            value: ['main::a.jpg'],
            meta: {
                container: { id: 'main', can_view: true, can_upload: true },
                data: [{ id: 'main::a.jpg' }],
            },
        },
    });
    await flushPromises();

    await field.setProps({ value: ['main::b.jpg'] });
    await flushPromises();

    expect(post).toHaveBeenCalledWith('assets-fieldtype', { assets: ['main::b.jpg'] });
    expect(field.vm.assets).toEqual([{ id: 'main::b.jpg' }]);
    expect(field.emitted('update:value')).toBeUndefined();
});

test('removing an asset emits an update', async () => {
    const field = makeField({
        props: {
            value: ['main::a.jpg', 'main::b.jpg'],
            meta: {
                container: { id: 'main', can_view: true, can_upload: true },
                data: [{ id: 'main::a.jpg' }, { id: 'main::b.jpg' }],
            },
        },
    });
    await flushPromises();

    field.vm.assetRemoved({ id: 'main::a.jpg' });
    await flushPromises();

    expect(field.emitted('update:value')).toEqual([[['main::b.jpg']]]);
});

const singleAssetField = (provide = {}) =>
    makeField({
        provide,
        props: {
            value: ['main::a.jpg'],
            config: { max_files: 1 },
            meta: {
                container: { id: 'main', can_view: true, can_upload: true },
                data: [{ id: 'main::a.jpg' }],
            },
        },
    });

test('an upload replaces the asset in a link field', async () => {
    const field = singleAssetField({ isInLinkField: true });
    await flushPromises();

    field.vm.uploadComplete({ id: 'main::b.jpg' });
    await flushPromises();
    field.vm.uploadComplete({ id: 'main::c.jpg' });
    await flushPromises();

    expect(field.emitted('update:value')).toEqual([[['main::b.jpg']], [['main::c.jpg']]]);
});

test('an upload is appended in a single asset field so validation can flag it', async () => {
    const field = singleAssetField();
    await flushPromises();

    field.vm.uploadComplete({ id: 'main::b.jpg' });

    expect(field.vm.assets).toEqual([{ id: 'main::a.jpg' }, { id: 'main::b.jpg' }]);
});

const compactField = ({ assets = [], config = {}, ...options } = {}) => makeField({
    ...options,
    props: {
        value: assets.map((asset) => asset.id),
        config: { mode: 'compact', max_files: 1, ...config },
        meta: {
            container: { id: 'main', can_view: true, can_upload: true },
            data: assets,
        },
    },
});

test('compact mode renders a thumbnail without the full asset picker or list', async () => {
    const field = compactField({
        assets: [{ id: 'main::a.jpg', basename: 'a.jpg', thumbnail: '/thumb/a.jpg' }],
    });
    await flushPromises();

    expect(field.findComponent(AssetCompact).props('asset').thumbnail).toBe('/thumb/a.jpg');
    expect(field.text()).not.toContain('Browse Assets');
    expect(field.find('table').exists()).toBe(false);
    expect(field.findComponent({ ref: 'uploader' }).attributes('style')).toContain('display: none');
});

test('an empty compact field opens the full asset selector', async () => {
    const field = compactField({ provide: { isInGridField: true } });
    await flushPromises();

    await field.findComponent(Button).vm.$emit('click');

    expect(field.vm.showSelector).toBe(true);
    expect(field.vm.isCompact).toBe(true);
    expect(field.vm.selectorMaxFiles).toBe(1);
});

test('replacing a compact asset preserves the other selections and their order', async () => {
    const post = vi.fn((url, { assets }) => Promise.resolve({ data: assets.map((id) => ({ id })) }));
    const field = compactField({
        axiosPost: post,
        assets: [{ id: 'main::a.jpg' }, { id: 'main::b.jpg' }, { id: 'main::c.jpg' }],
        config: { max_files: 3 },
    });
    await flushPromises();

    await field.findAllComponents(AssetCompact)[1].vm.$emit('replace');

    expect(field.vm.showSelector).toBe(true);
    expect(field.vm.selectorMaxFiles).toBe(1);
    expect(field.vm.selectedAssets).toEqual(['main::b.jpg']);

    field.vm.assetsSelected(['main::new.jpg']);
    await flushPromises();

    expect(post).toHaveBeenCalledWith('assets-fieldtype', {
        assets: ['main::a.jpg', 'main::new.jpg', 'main::c.jpg'],
    });
    expect(field.emitted('update:value')).toEqual([[['main::a.jpg', 'main::new.jpg', 'main::c.jpg']]]);
});

test('canceling replacement keeps the existing compact asset selected', async () => {
    const field = compactField({ assets: [{ id: 'main::a.jpg' }] });
    await flushPromises();

    field.vm.openReplacementSelector(field.vm.assets[0]);
    await field.vm.$nextTick();
    field.vm.showSelector = false;
    await field.vm.$nextTick();

    expect(field.vm.replacementAssetId).toBeNull();
    expect(field.vm.assets).toEqual([{ id: 'main::a.jpg' }]);
    expect(field.emitted('update:value')).toBeUndefined();
});

test('removing the compact thumbnail restores the empty picker button', async () => {
    const field = compactField({ assets: [{ id: 'main::a.jpg' }] });
    await flushPromises();

    await field.findComponent(AssetCompact).vm.$emit('removed');
    await flushPromises();

    expect(field.emitted('update:value')).toEqual([[[]]]);
    expect(field.findComponent(Button).exists()).toBe(true);
});

test.each([
    { disabled: true },
    { visibility: 'read_only' },
])('compact mode cannot open the selector when configured as %o', async (config) => {
    const field = compactField({ config });
    await flushPromises();

    field.vm.openSelector();

    expect(field.vm.showSelector).toBe(false);
});

test.each([
    { assets: [] },
    { assets: [{ id: 'main::a.jpg' }] },
])('compact fields only accept file drops on the plus button with selections $assets', async ({ assets }) => {
    const field = compactField({
        assets,
        config: { allow_uploads: true, max_files: 3 },
        stubs: { Uploader: false },
    });
    await flushPromises();

    const uploader = field.findComponent(Uploader);
    const addFile = vi.spyOn(uploader.vm, 'addFile').mockImplementation(() => {});
    const file = new File(['image'], 'new.jpg', { type: 'image/jpeg' });
    const dragEvent = (type) => {
        const event = new Event(type, { bubbles: true, cancelable: true });

        Object.defineProperty(event, 'dataTransfer', {
            value: { types: ['Files'], files: [file], items: [] },
        });

        return event;
    };

    expect(uploader.findComponent(AssetCompact).exists()).toBe(false);

    if (assets.length) {
        field.findComponent(AssetCompact).element.dispatchEvent(dragEvent('drop'));

        expect(addFile).not.toHaveBeenCalled();
    }

    uploader.element.dispatchEvent(dragEvent('dragenter'));
    await field.vm.$nextTick();

    const overlay = field.find('[role="status"]');

    expect(overlay.isVisible()).toBe(true);
    expect(overlay.text()).toBe('');

    uploader.element.dispatchEvent(dragEvent('drop'));
    await field.vm.$nextTick();

    expect(addFile).toHaveBeenCalledWith(file);
    expect(uploader.vm.dragging).toBe(false);
    expect(field.find('[role="status"]').attributes('style')).toContain('display: none');

    uploader.vm.$emit('upload-complete', { id: 'main::new.jpg' });
    await flushPromises();

    expect(field.emitted('update:value')).toEqual([[[...assets.map((asset) => asset.id), 'main::new.jpg']]]);
});

test('compact uploads stay inline and hide the plus button until they finish', async () => {
    const field = compactField({ config: { allow_uploads: true } });
    await flushPromises();

    field.vm.uploadsUpdated([{
        id: 'upload-1',
        basename: 'new.jpg',
        percent: 45,
        retry: vi.fn(),
    }]);
    await field.vm.$nextTick();

    expect(field.findComponent(AssetUpload).attributes()).toMatchObject({ basename: 'new.jpg', percent: '45' });
    expect(field.findComponent(Uploads).exists()).toBe(false);
    expect(field.findComponent({ ref: 'uploader' }).attributes('style')).toContain('display: none');
});

test.each([
    { option: 'overwrite' },
    { option: 'timestamp' },
    { option: 'rename', filename: 'renamed' },
])('compact fields can retry a queued conflict with %o after reaching the file limit', async (options) => {
    const field = compactField({
        config: { allow_uploads: true },
        stubs: { Uploader: false },
    });
    await flushPromises();

    const uploader = field.findComponent(Uploader);
    const firstAsset = { id: 'main::a.jpg' };
    const secondAsset = { id: 'main::b.jpg' };
    const conflictFile = new File(['b'], 'b.jpg', { type: 'image/jpeg' });
    const responses = [
        { status: 200, data: JSON.stringify({ data: firstAsset }) },
        {
            status: 409,
            data: JSON.stringify({
                message: 'File already exists.',
                errors: { file: ['File already exists.'] },
                existing: secondAsset.id,
            }),
        },
        { status: 200, data: JSON.stringify({ data: secondAsset }) },
    ];
    const makeUpload = vi.spyOn(uploader.vm, 'makeUpload').mockImplementation((id, file) => {
        const upload = {
            state: 'new',
            form: new Map([['file', file]]),
            upload() {
                upload.state = 'started';

                return Promise.resolve(responses.shift()).then((response) => {
                    upload.state = 'done';

                    return response;
                });
            },
        };

        return upload;
    });

    uploader.vm.addFile(new File(['a'], 'a.jpg', { type: 'image/jpeg' }));
    uploader.vm.addFile(conflictFile);
    await flushPromises();

    expect(field.vm.assets).toEqual([firstAsset]);
    expect(uploader.props('enabled')).toBe(false);
    expect(field.vm.uploads[0].errorStatus).toBe(409);

    uploader.vm.addFile(new File(['c'], 'c.jpg', { type: 'image/jpeg' }));

    expect(makeUpload).toHaveBeenCalledTimes(2);

    field.findComponent(AssetUpload).vm.$emit('retry', options);
    await flushPromises();

    expect(makeUpload).toHaveBeenLastCalledWith(expect.any(String), conflictFile, options);
    expect(field.vm.uploads).toEqual([]);
    expect(field.emitted('update:value')).toEqual([
        [[firstAsset.id]],
        [[firstAsset.id, secondAsset.id]],
    ]);
});

test('a full compact field does not allow drops that would replace its selection', async () => {
    const field = compactField({
        assets: [{ id: 'main::a.jpg' }],
        config: { allow_uploads: true },
    });
    await flushPromises();

    expect(field.vm.canReceiveUpload).toBe(false);
    expect(field.findComponent({ ref: 'uploader' }).attributes('style')).toContain('display: none');
});

test.each([
    { allow_uploads: false },
    { allow_uploads: true, disabled: true },
    { allow_uploads: true, visibility: 'read_only' },
])('compact upload permission is respected for %o', async (config) => {
    const field = compactField({ config });
    await flushPromises();

    expect(field.vm.canReceiveUpload).toBe(false);
});

test('full mode shows the first asset at full size and additional assets as compact thumbnails', async () => {
    const assets = [{ id: 'main::a.jpg' }, { id: 'main::b.jpg' }];
    const field = compactField({
        assets,
        config: { mode: 'full', max_files: 3, aspect_ratio: '4/3' },
        stubs: { AssetFull: false, Uploader: false },
    });
    await flushPromises();

    expect(field.findComponent(AssetFull).props()).toMatchObject({ asset: assets[0], aspectRatio: '4/3' });
    expect(field.findAllComponents(AssetCompact).map((component) => component.props('asset'))).toEqual([assets[1]]);
    expect(field.findComponent({ ref: 'uploader' }).isVisible()).toBe(true);

    await field.setProps({ config: { mode: 'full', max_files: 2 } });

    expect(field.findComponent({ ref: 'uploader' }).attributes('style')).toContain('display: none');
});

test('an empty full field keeps its large drop target visible during an upload', async () => {
    const field = compactField({ config: { mode: 'full', allow_uploads: true } });
    await flushPromises();

    const uploader = field.findComponent({ ref: 'uploader' });

    expect(uploader.element.style.display).not.toBe('none');
    expect(uploader.find('[style]').element.style.getPropertyValue('--preview-aspect-ratio')).toBe('8/3');

    field.vm.uploadsUpdated([{ id: 'upload-1', basename: 'new.jpg', percent: 45, retry: vi.fn() }]);
    await field.vm.$nextTick();

    expect(uploader.isVisible()).toBe(true);
    expect(field.findComponent(Upload).props('percent')).toBe(45);
    expect(field.findComponent(AssetUpload).exists()).toBe(false);
});

test('selecting an existing file in a full field preserves other pending uploads', async () => {
    const field = compactField({ config: { mode: 'full', max_files: 3, allow_uploads: true } });
    await flushPromises();

    const pending = { id: 'upload-2', basename: 'next.jpg', percent: 45, retry: vi.fn() };

    field.vm.uploadsUpdated([
        { id: 'upload-1', basename: 'existing.jpg', existingId: 'main::existing.jpg', errorStatus: 409, retry: vi.fn() },
        pending,
    ]);
    await field.vm.$nextTick();

    field.findComponent(Upload).vm.$emit('existing-selected');
    await flushPromises();

    expect(field.vm.uploads).toEqual([pending]);
});
