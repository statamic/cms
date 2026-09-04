import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import VideoFieldtype from '@/components/fieldtypes/VideoFieldtype.vue';
import { publishContextKey } from '@/components/ui';

window.__ = (key) => key;

let intersect;
let axios;
let toast;

beforeEach(() => {
    window.IntersectionObserver = class {
        constructor(callback) {
            intersect = () => callback([{ isIntersecting: true, intersectionRatio: 1 }]);
        }
        observe() {}
        disconnect() {}
    };

    axios = { get: vi.fn() };
    toast = { error: vi.fn() };
});

afterEach(() => {
    vi.restoreAllMocks();
});

const stub = (tag) => ({
    props: ['modelValue'],
    emits: ['update:modelValue'],
    template: `<${tag} :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" />`,
});

const mountVideoField = (props = {}) => {
    return mount(VideoFieldtype, {
        props: {
            handle: 'video',
            config: {},
            meta: {
                url: '/cp/video/details',
                providers: [{ provider: 'Cloudflare' }, { provider: 'Youtube' }],
            },
            ...props,
        },
        global: {
            provide: { [publishContextKey]: {} },
            mocks: { $axios: axios, $toast: toast },
            stubs: {
                'ui-combobox': stub('select'),
                'ui-input': stub('input'),
            },
        },
    });
};

test('cloudflare id is sent with the cloudflare prefix', async () => {
    axios.get.mockResolvedValue({ data: { embed: '<iframe></iframe>', provider: 'Cloudflare' } });

    const wrapper = mountVideoField({ meta: { url: '/cp/video/details', providers: [], provider: 'Cloudflare' } });

    await wrapper.find('input').setValue('1234');
    await flushPromises();

    expect(axios.get).toHaveBeenCalledWith('/cp/video/details', { params: { url: 'cloudflare:1234' } });
    expect(wrapper.emitted('update:value')[0]).toEqual(['cloudflare:1234']);
});

test('embed survives the provider being corrected by the lookup', async () => {
    axios.get.mockResolvedValue({ data: { embed: '<iframe></iframe>', provider: 'Youtube' } });

    const wrapper = mountVideoField();
    intersect();

    await wrapper.find('input').setValue('https://www.youtube.com/watch?v=FK3dav4bA4s');
    await flushPromises();

    expect(wrapper.vm.provider).toBe('Youtube');
    expect(wrapper.find('input').element.value).toBe('https://www.youtube.com/watch?v=FK3dav4bA4s');
    expect(wrapper.find('iframe').exists()).toBe(true);
});

test('changing the provider clears the embed and url', async () => {
    const wrapper = mountVideoField({
        value: 'https://www.youtube.com/watch?v=FK3dav4bA4s',
        meta: { url: '/cp/video/details', providers: [], provider: 'Youtube', embed: '<iframe></iframe>' },
    });
    intersect();
    await flushPromises();
    expect(wrapper.find('iframe').exists()).toBe(true);

    await wrapper.find('select').setValue('Cloudflare');

    expect(wrapper.find('iframe').exists()).toBe(false);
    expect(wrapper.vm.url).toBeNull();
});

test('embed is not rendered until the field is visible', async () => {
    const wrapper = mountVideoField({
        value: 'cloudflare:1234',
        meta: { url: '/cp/video/details', providers: [], provider: 'Cloudflare', embed: '<iframe></iframe>' },
    });

    expect(wrapper.find('iframe').exists()).toBe(false);

    intersect();
    await flushPromises();

    expect(wrapper.find('iframe').exists()).toBe(true);
});

test('failed lookup clears the embed and shows an error', async () => {
    axios.get.mockRejectedValue({ response: { data: { message: 'Nope' } } });

    const wrapper = mountVideoField({
        value: 'cloudflare:1234',
        meta: { url: '/cp/video/details', providers: [], provider: 'Cloudflare', embed: '<iframe></iframe>' },
    });
    intersect();

    await wrapper.find('input').setValue('5678');
    await flushPromises();

    expect(wrapper.find('iframe').exists()).toBe(false);
    expect(toast.error).toHaveBeenCalledWith('Nope');
});
