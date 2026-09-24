import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import VideoFieldtype from '@/components/fieldtypes/VideoFieldtype.vue';
import { publishContextKey } from '@/components/ui';

window.__ = (key) => key;

beforeEach(() => {
    vi.useFakeTimers();

    window.IntersectionObserver = class {
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
