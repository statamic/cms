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

const makeField = ({ axiosPost = vi.fn(() => Promise.resolve({ data: [] })), props = {} } = {}) => {
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
            mocks: {
                $axios: { post: axiosPost },
                $progress: { loading: () => {} },
            },
        },
    });
};

test('selecting the existing file references it by the container id', () => {
    const post = vi.fn(() => Promise.resolve({ data: [] }));
    const field = makeField({ axiosPost: post });

    field.vm.uploadSelected({ basename: 'user.png' });

    expect(post).toHaveBeenCalledWith('assets-fieldtype', { assets: ['main::user.png'] });
});

test('the existing file id includes the configured folder', () => {
    const post = vi.fn(() => Promise.resolve({ data: [] }));
    const field = makeField({ axiosPost: post, props: { config: { folder: 'sub' } } });

    field.vm.uploadSelected({ basename: 'user.png' });

    expect(post).toHaveBeenCalledWith('assets-fieldtype', { assets: ['main::sub/user.png'] });
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
