import { flushPromises, mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import { preferences } from '@api';
import AssetsFieldtype from '@/components/fieldtypes/assets/AssetsFieldtype.vue';
import AssetCompact from '@/components/fieldtypes/assets/AssetCompact.vue';
import AssetUpload from '@/components/fieldtypes/assets/AssetUpload.vue';
import Uploader from '@/components/assets/Uploader.vue';
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
