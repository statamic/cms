import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import VideoFieldtype from '@/components/fieldtypes/VideoFieldtype.vue';
import { publishContextKey } from '@/components/ui';

window.__ = (key) => key;

let intersect;

beforeEach(() => {
    vi.useFakeTimers();

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
            meta: {},
            ...props,
        },
        global: {
            provide: { [publishContextKey]: {} },
            stubs: {
                'ui-input': stub('input'),
                'ui-input-group': { template: '<div><slot /></div>' },
                'ui-input-group-prepend': { template: '<span />' },
                'ui-description': { template: '<p><slot /></p>' },
            },
        },
    });
};

const cloudflare = 'https://customer-wve7oze7jjxdb0j6.cloudflarestream.com/b209b1484821fff0ed2e5535fbb4ff7b';

const preview = async (value) => {
    const wrapper = mountVideoField({ value });
    intersect();
    await wrapper.vm.$nextTick();

    return wrapper.find('iframe');
};

test('it previews a cloudflare manifest url as its iframe', async () => {
    expect((await preview(`${cloudflare}/manifest/video.m3u8`)).attributes('src')).toBe(`${cloudflare}/iframe`);
});

test('it previews a cloudflare iframe url', async () => {
    expect((await preview(`${cloudflare}/iframe`)).attributes('src')).toBe(`${cloudflare}/iframe`);
});

test('it does not preview a cloudflare url with a malformed id', async () => {
    expect((await preview('https://customer-wve7oze7jjxdb0j6.cloudflarestream.com/1234"><script>alert(1)</script>')).exists()).toBe(false);
});

test('it debounces updates while typing', async () => {
    const wrapper = mountVideoField({ value: null });
    const input = wrapper.find('input');

    await input.setValue('https://www.youtube.com/watch?v=1');
    await input.setValue('https://www.youtube.com/watch?v=12');
    await input.setValue('https://www.youtube.com/watch?v=123');

    expect(wrapper.emitted('update:value')).toBeUndefined();

    vi.runAllTimers();

    expect(wrapper.emitted('update:value')).toHaveLength(1);
    expect(wrapper.emitted('update:value')[0]).toEqual(['https://www.youtube.com/watch?v=123']);
});
