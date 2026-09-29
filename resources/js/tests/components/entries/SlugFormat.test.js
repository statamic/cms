import { shallowMount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import PublishForm from '@/components/entries/PublishForm.vue';

window.__ = (key) => key;
window.Statamic = { $commandPalette: { add: () => {}, category: {}, remove: () => {} } };

const ContainerStub = defineComponent({ render: () => h('div') });

let post;

function mountForm(values, slugFormat, meta = {}) {
    return shallowMount(PublishForm, {
        props: {
            publishContainer: 'base',
            initialFieldset: { handle: 'article', tabs: [] },
            initialValues: values,
            initialMeta: meta,
            initialLocalizations: [],
            initialSlugFormat: slugFormat,
            collectionHandle: 'articles',
            initialActions: {},
            method: 'post',
        },
        global: {
            stubs: { PublishContainer: ContainerStub },
            mocks: {
                $axios: { post },
                $progress: { isComplete: () => true, loading: () => {} },
                $config: { get: () => 'ltr' },
                $preferences: { get: () => null },
                $keys: { bindGlobal: () => {}, unbind: () => {} },
                $events: { $on: () => {}, $off: () => {}, $emit: () => {} },
            },
        },
    });
}

beforeEach(() => {
    vi.useFakeTimers();
    post = vi.fn(() => Promise.resolve({ data: { slug: '56-summer-2026' } }));
});

afterEach(() => {
    vi.useRealTimers();
});

test('the slug source is generated from the fields the format references', async () => {
    const wrapper = mountForm(
        { title: 'Summer 2026', slug: null, issue: null, body: 'Lorem ipsum' },
        { url: '/slug-format', fields: ['issue', 'title'] },
    );

    expect(wrapper.vm.slugSource).toBe('');

    await wrapper.setData({ values: { issue: 56 } });

    await vi.advanceTimersByTimeAsync(299);
    expect(post).not.toHaveBeenCalled();

    await vi.advanceTimersByTimeAsync(1);
    expect(post).toHaveBeenCalledOnce();
    expect(post.mock.calls[0][0]).toBe('/slug-format');
    expect(post.mock.calls[0][1]).toEqual({
        blueprint: 'article',
        values: { issue: 56, title: 'Summer 2026' },
    });

    expect(wrapper.vm.slugSource).toBe('56-summer-2026');
});

test('the slug source is not generated when a field the format ignores changes', async () => {
    const wrapper = mountForm(
        { title: 'Summer 2026', slug: null, issue: 56, body: 'Lorem ipsum' },
        { url: '/slug-format', fields: ['issue', 'title'] },
    );

    await wrapper.setData({ values: { body: 'Dolor sit amet' } });
    await vi.advanceTimersByTimeAsync(300);

    expect(post).not.toHaveBeenCalled();
});

test('the slug is never submitted, even when the format references it', async () => {
    const wrapper = mountForm(
        { title: 'Summer 2026', slug: null, issue: 56 },
        { url: '/slug-format', fields: ['slug', 'issue'] },
    );

    await wrapper.setData({ values: { issue: 57 } });
    await vi.advanceTimersByTimeAsync(300);

    expect(post).toHaveBeenCalledOnce();
    expect(post.mock.calls[0][1].values).toEqual({ issue: 57 });

    await wrapper.setData({ values: { slug: '57-summer-2026' } });
    await vi.advanceTimersByTimeAsync(300);

    expect(post).toHaveBeenCalledOnce();
});

test('the slug source is not generated once the user has taken over the slug', async () => {
    const wrapper = mountForm(
        { title: 'Summer 2026', slug: 'custom', issue: 56 },
        { url: '/slug-format', fields: ['issue', 'title'] },
        { slug: { auto: false } },
    );

    await wrapper.setData({ values: { issue: 57 } });
    await vi.advanceTimersByTimeAsync(300);

    expect(post).not.toHaveBeenCalled();
});

test('the slug source is never generated without a slug format', async () => {
    const wrapper = mountForm({ title: 'Summer 2026', slug: null, issue: 56 }, null);

    expect(wrapper.vm.slugSource).toBeNull();

    await wrapper.setData({ values: { issue: 57 } });
    await vi.advanceTimersByTimeAsync(300);

    expect(post).not.toHaveBeenCalled();
});
