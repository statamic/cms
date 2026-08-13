import { shallowMount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import PublishForm from '@/components/entries/PublishForm.vue';

window.__ = (key) => key;
window.Statamic = { $commandPalette: { add: () => {}, category: {}, remove: () => {} } };

const ContainerStub = defineComponent({
    methods: {
        setFieldValue(handle, value) {
            this.$attrs.modelValue[handle] = value;
        },
    },
    render: () => h('div'),
});

let post;

function mountForm(values, titleFormat) {
    return shallowMount(PublishForm, {
        props: {
            publishContainer: 'base',
            initialFieldset: { handle: 'article', tabs: [] },
            initialValues: values,
            initialMeta: {},
            initialLocalizations: [],
            initialTitleFormat: titleFormat,
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
    post = vi.fn(() => Promise.resolve({ data: { title: 'Michael Aerni' } }));
});

afterEach(() => {
    vi.useRealTimers();
});

test('the title is generated from the fields the format references', async () => {
    const wrapper = mountForm(
        { title: null, slug: null, first_name: 'Michael', last_name: null, body: 'Lorem ipsum' },
        { url: '/title-format', fields: ['first_name', 'last_name'] },
    );

    await wrapper.setData({ values: { last_name: 'Aerni' } });

    await vi.advanceTimersByTimeAsync(299);
    expect(post).not.toHaveBeenCalled();

    await vi.advanceTimersByTimeAsync(1);
    expect(post).toHaveBeenCalledOnce();
    expect(post.mock.calls[0][0]).toBe('/title-format');
    expect(post.mock.calls[0][1]).toEqual({
        blueprint: 'article',
        values: { first_name: 'Michael', last_name: 'Aerni' },
    });

    expect(wrapper.vm.values.title).toBe('Michael Aerni');
});

test('the title is not generated when a field the format ignores changes', async () => {
    const wrapper = mountForm(
        { title: null, slug: null, first_name: 'Michael', body: 'Lorem ipsum' },
        { url: '/title-format', fields: ['first_name'] },
    );

    await wrapper.setData({ values: { body: 'Dolor sit amet' } });
    await vi.advanceTimersByTimeAsync(300);

    expect(post).not.toHaveBeenCalled();
});

test('the generated title does not trigger another request', async () => {
    const wrapper = mountForm(
        { title: null, slug: null, first_name: 'Michael', last_name: 'Aerni' },
        { url: '/title-format', fields: ['first_name', 'last_name'] },
    );

    await wrapper.setData({ values: { first_name: 'Ruth' } });
    await vi.advanceTimersByTimeAsync(300);
    expect(post).toHaveBeenCalledOnce();

    await vi.advanceTimersByTimeAsync(300);
    expect(post).toHaveBeenCalledOnce();
});

test('the title is never generated without a title format', async () => {
    const wrapper = mountForm({ title: 'Michael Aerni', slug: null, first_name: 'Michael' }, null);

    await wrapper.setData({ values: { first_name: 'Ruth' } });
    await vi.advanceTimersByTimeAsync(300);

    expect(post).not.toHaveBeenCalled();
});
