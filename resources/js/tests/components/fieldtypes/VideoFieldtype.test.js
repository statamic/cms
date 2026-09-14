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

    window.IntersectionObserver = class {
        constructor(callback) {
            intersect = () => callback([{ isIntersecting: true, intersectionRatio: 1 }]);
        }
        observe() {}
        disconnect() {}
    };

    axios = { get: vi.fn().mockResolvedValue({ data: {} }) };
    toast = { error: vi.fn() };
});

afterEach(() => {
    vi.useRealTimers();
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
                providers: [
                    { value: 'cloudflare', label: 'Cloudflare Stream' },
                    { value: 'Youtube', label: 'Youtube' },
                ],
            },
            ...props,
        },
        global: {
            provide: { [publishContextKey]: {} },
            mocks: { $axios: axios, $toast: toast },
            stubs: {
                'ui-combobox': stub('select'),
                'ui-input': stub('input'),
                'ui-description': { template: '<p><slot /></p>' },
            },
        },
    });
};

// Typing debounces, so the update is only emitted once the timers run.
const type = async (wrapper, value) => {
    await wrapper.find('input').setValue(value);
    vi.runAllTimers();
    await flushPromises();
};

test('it renders the embed from preloaded meta once visible', async () => {
    const wrapper = mountVideoField({
        value: 'https://www.youtube.com/watch?v=1234',
        meta: {
            url: '/cp/video/details',
            providers: [],
            video: { provider: 'Youtube', embed_url: 'https://www.youtube.com/embed/1234' },
        },
    });

    expect(wrapper.find('iframe').exists()).toBe(false);

    intersect();
    await flushPromises();

    expect(wrapper.find('iframe').attributes('src')).toBe('https://www.youtube.com/embed/1234');
});

test('it debounces lookups while typing', async () => {
    const wrapper = mountVideoField({ value: null });

    await wrapper.find('input').setValue('https://www.youtube.com/watch?v=1');
    await wrapper.find('input').setValue('https://www.youtube.com/watch?v=12');
    await wrapper.find('input').setValue('https://www.youtube.com/watch?v=123');

    expect(wrapper.emitted('update:value')).toBeUndefined();

    vi.runAllTimers();

    expect(wrapper.emitted('update:value')).toHaveLength(1);
    expect(wrapper.emitted('update:value')[0]).toEqual(['https://www.youtube.com/watch?v=123']);
});

test('it sends the cloudflare prefix with the stored value', async () => {
    const wrapper = mountVideoField({ value: 'cloudflare:oldid' });

    await type(wrapper, '1234');

    expect(wrapper.emitted('update:value')[0]).toEqual(['cloudflare:1234']);
});

test('it splits the stored value into the url and id inputs', () => {
    expect(mountVideoField({ value: 'https://vimeo.com/1' }).find('input').element.value).toBe('https://vimeo.com/1');
    expect(mountVideoField({ value: 'cloudflare:1234' }).find('input').element.value).toBe('1234');
});

test('it re-syncs the input when the value changes externally', async () => {
    const wrapper = mountVideoField({ value: 'https://vimeo.com/1' });

    await wrapper.setProps({ value: 'https://vimeo.com/2' });

    expect(wrapper.find('input').element.value).toBe('https://vimeo.com/2');
});

test('it clears the stored value when the provider changes', async () => {
    const wrapper = mountVideoField({ value: 'https://www.youtube.com/watch?v=1234' });

    await wrapper.find('select').setValue('cloudflare');

    expect(wrapper.emitted('update:value')).toHaveLength(1);
    expect(wrapper.emitted('update:value')[0]).toEqual([null]);
    expect(wrapper.find('iframe').exists()).toBe(false);
});

test('it looks up details when the value changes', async () => {
    axios.get.mockResolvedValue({
        data: { provider: 'Youtube', embed_url: 'https://www.youtube.com/embed/1234' },
    });

    const wrapper = mountVideoField({ value: null });
    intersect();

    await wrapper.setProps({ value: 'https://www.youtube.com/watch?v=1234' });
    await flushPromises();

    expect(axios.get).toHaveBeenCalledWith('/cp/video/details', expect.objectContaining({
        params: { value: 'https://www.youtube.com/watch?v=1234' },
    }));
    expect(wrapper.find('iframe').attributes('src')).toBe('https://www.youtube.com/embed/1234');
});

test('it discards a response that resolves after the value moved on', async () => {
    let resolveStale;
    axios.get
        .mockImplementationOnce(() => new Promise((resolve) => (resolveStale = resolve)))
        .mockResolvedValueOnce({ data: { provider: 'Vimeo', embed_url: 'https://player.vimeo.com/video/2' } });

    const wrapper = mountVideoField({ value: null });
    intersect();

    await wrapper.setProps({ value: 'https://www.youtube.com/watch?v=1' });
    await wrapper.setProps({ value: 'https://vimeo.com/2' });
    await flushPromises();

    // The first lookup lands last, but belongs to a value that is no longer current.
    resolveStale({ data: { provider: 'Youtube', embed_url: 'https://www.youtube.com/embed/1' } });
    await flushPromises();

    expect(wrapper.vm.provider).toBe('Vimeo');
    expect(wrapper.find('iframe').attributes('src')).toBe('https://player.vimeo.com/video/2');
});

test('it renders a video element for a direct file', async () => {
    axios.get.mockResolvedValue({
        data: { provider: 'file', embed_url: 'https://example.com/clip.mp4' },
    });

    const wrapper = mountVideoField({ value: null });
    intersect();

    await wrapper.setProps({ value: 'https://example.com/clip.mp4' });
    await flushPromises();

    expect(wrapper.find('video').attributes('src')).toBe('https://example.com/clip.mp4');
    expect(wrapper.find('iframe').exists()).toBe(false);
});

test('a failed lookup clears the embed and shows an error', async () => {
    axios.get.mockRejectedValue({ response: { data: { message: 'Nope' } } });

    const wrapper = mountVideoField({
        value: 'https://www.youtube.com/watch?v=1234',
        meta: {
            url: '/cp/video/details',
            providers: [],
            video: { provider: 'Youtube', embed_url: 'https://www.youtube.com/embed/1234' },
        },
    });
    intersect();

    await wrapper.setProps({ value: 'https://www.youtube.com/watch?v=5678' });
    await flushPromises();

    expect(wrapper.find('iframe').exists()).toBe(false);
    expect(toast.error).toHaveBeenCalledWith('Nope');
});
