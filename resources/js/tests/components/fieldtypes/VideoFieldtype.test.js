import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import VideoFieldtype from '@/components/fieldtypes/VideoFieldtype.vue';
import { publishContextKey } from '@/components/ui';

window.__ = (key) => key;

let intersect;
let axios;
let toast;

beforeEach(() => {
    vi.useFakeTimers();

    axios = { get: vi.fn().mockResolvedValue({ data: {} }) };
    toast = { error: vi.fn() };

    window.IntersectionObserver = class {
        constructor(callback) {
            intersect = () => callback([{ isIntersecting: true, intersectionRatio: 1 }]);
        }
        observe() {}
        disconnect() {}
    };
});

afterEach(() => {
    vi.useRealTimers();
    vi.restoreAllMocks();
});

const providers = [
    { value: 'url', label: 'URL' },
    { value: 'cloudflare', label: 'Cloudflare Stream' },
];

const stub = (tag) => ({
    props: ['modelValue'],
    emits: ['update:modelValue'],
    template: `<${tag} :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" />`,
});

const mountVideoField = (value = null, video = null) => {
    return mount(VideoFieldtype, {
        props: { handle: 'video', config: {}, meta: { providers, video, detailsUrl: '/cp/video/details' }, value },
        global: {
            provide: { [publishContextKey]: {} },
            mocks: { $axios: axios, $toast: toast },
            stubs: {
                'ui-combobox': stub('select'),
                'ui-input': stub('input'),
                'ui-input-group': { template: '<div><slot /></div>' },
                'ui-input-group-prepend': { template: '<span />' },
                'ui-description': { template: '<p><slot /></p>' },
            },
        },
    });
};

test('it shows the id input for a cloudflare value', () => {
    const wrapper = mountVideoField('cloudflare:abc123');

    expect(wrapper.find('input').element.value).toBe('abc123');
});

test('it shows the url input for a url value', () => {
    const wrapper = mountVideoField('https://vimeo.com/1');

    expect(wrapper.find('input').element.value).toBe('https://vimeo.com/1');
});

test('it stores the cloudflare id with a prefix', async () => {
    const wrapper = mountVideoField('cloudflare:old');

    await wrapper.find('input').setValue('abc123');

    expect(wrapper.emitted('update:value')[0]).toEqual(['cloudflare:abc123']);
});

test('it previews a cloudflare video', async () => {
    const wrapper = mountVideoField('cloudflare:abc123');
    intersect();
    await wrapper.vm.$nextTick();

    expect(wrapper.find('iframe').attributes('src')).toBe('https://iframe.cloudflarestream.com/abc123');
});

test('it clears the stored value when switching provider', async () => {
    const wrapper = mountVideoField('https://www.youtube.com/watch?v=1234');

    await wrapper.find('select').setValue('cloudflare');

    expect(wrapper.emitted('update:value')).toHaveLength(1);
    expect(wrapper.emitted('update:value')[0]).toEqual([null]);
});

test('it does not emit when switching to the provider already in use', async () => {
    const wrapper = mountVideoField(null);

    await wrapper.find('select').setValue('url');

    expect(wrapper.emitted('update:value')).toBeUndefined();
});

test('it rejects a malformed cloudflare id', async () => {
    const wrapper = mountVideoField('cloudflare:abc"><script>alert(1)</script>');
    intersect();
    await wrapper.vm.$nextTick();

    expect(wrapper.find('iframe').exists()).toBe(false);
    expect(wrapper.find('p').exists()).toBe(true);
});

test('it uses the provider preloaded in meta for an empty field', () => {
    const wrapper = mountVideoField(null, { provider: 'cloudflare', url: null });

    expect(wrapper.vm.isCloudflare).toBe(true);
});

test('it debounces updates while typing', async () => {
    const wrapper = mountVideoField(null);
    const input = wrapper.find('input');

    await input.setValue('https://www.youtube.com/watch?v=1');
    await input.setValue('https://www.youtube.com/watch?v=12');
    await input.setValue('https://www.youtube.com/watch?v=123');

    expect(wrapper.emitted('update:value')).toBeUndefined();

    vi.runAllTimers();

    expect(wrapper.emitted('update:value')).toHaveLength(1);
    expect(wrapper.emitted('update:value')[0]).toEqual(['https://www.youtube.com/watch?v=123']);
});

test('it looks up details for a url value', async () => {
    axios.get.mockResolvedValue({
        data: { provider: 'Youtube', url: 'https://www.youtube.com/watch?v=1234', embed_url: 'https://www.youtube.com/embed/1234' },
    });

    const wrapper = mountVideoField('https://www.youtube.com/watch?v=1234');
    intersect();
    await flushPromises();

    expect(axios.get).toHaveBeenCalledWith('/cp/video/details', expect.objectContaining({
        params: { value: 'https://www.youtube.com/watch?v=1234' },
    }));
    expect(wrapper.find('iframe').attributes('src')).toBe('https://www.youtube.com/embed/1234');
});

test('it does not look up a cloudflare value', async () => {
    mountVideoField('cloudflare:abc123');
    await flushPromises();

    expect(axios.get).not.toHaveBeenCalled();
});

test('it discards a response that resolves after the value moved on', async () => {
    let resolveStale;
    axios.get
        .mockImplementationOnce(() => new Promise((resolve) => (resolveStale = resolve)))
        .mockResolvedValueOnce({
            data: { provider: 'Vimeo', url: 'https://vimeo.com/2', embed_url: 'https://player.vimeo.com/video/2' },
        });

    const wrapper = mountVideoField('https://www.youtube.com/watch?v=1');
    intersect();

    await wrapper.setProps({ value: 'https://vimeo.com/2' });
    await flushPromises();

    resolveStale({ data: { provider: 'Youtube', url: 'https://www.youtube.com/watch?v=1', embed_url: 'https://www.youtube.com/embed/1' } });
    await flushPromises();

    expect(wrapper.find('iframe').attributes('src')).toBe('https://player.vimeo.com/video/2');
});

test('a failed lookup shows an error', async () => {
    axios.get.mockRejectedValue({ response: { data: { message: 'Nope' } } });

    const wrapper = mountVideoField('https://www.youtube.com/watch?v=1234');
    intersect();
    await flushPromises();

    expect(wrapper.find('iframe').exists()).toBe(false);
    expect(toast.error).toHaveBeenCalledWith('Nope');
});

test('it renders a direct video file in a video element', async () => {
    axios.get.mockResolvedValue({
        data: { provider: 'file', url: 'https://example.com/clip.mp4', embed_url: 'https://example.com/clip.mp4' },
    });

    const wrapper = mountVideoField('https://example.com/clip.mp4');
    intersect();
    await flushPromises();

    expect(wrapper.find('video').attributes('src')).toBe('https://example.com/clip.mp4');
    expect(wrapper.find('iframe').exists()).toBe(false);
});
