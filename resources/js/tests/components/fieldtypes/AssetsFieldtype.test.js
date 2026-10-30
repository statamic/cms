import { flushPromises, mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import { preferences } from '@api';
import AssetsFieldtype from '@/components/fieldtypes/assets/AssetsFieldtype.vue';

// Register the global helper functions (data_get, clone, etc.) onto the window
// like the control panel bootstrap does, so the component can use them.
Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.cp_url = (url) => url;
window.__ = (key) => key;
preferences.initialize({}, {});

const makeField = ({ axiosPost = vi.fn(() => Promise.resolve({ data: [] })), props = {}, provide = {} } = {}) => {
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
