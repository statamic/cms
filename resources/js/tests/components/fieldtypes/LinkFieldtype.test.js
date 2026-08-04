import { flushPromises, mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import * as Globals from '@/bootstrap/globals';
import LinkFieldtype from '@/components/fieldtypes/LinkFieldtype.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));
window.__ = (key) => key;
window.Statamic = { $config: { get: (key) => ({ cpUrl: '/cp' })[key] } };

function mountField({ value = null, config = {}, meta = {}, showFieldPreviews = false, axiosPost } = {}) {
    return mount(LinkFieldtype, {
        shallow: true,
        props: {
            value,
            handle: 'link',
            config,
            meta: {
                initialOption: null,
                initialUrl: '',
                showFirstChildOption: false,
                types: {},
                ...meta,
            },
            showFieldPreviews,
        },
        global: {
            mocks: {
                $axios: { post: axiosPost ?? vi.fn(() => Promise.resolve({ data: { meta: {} } })) },
            },
        },
    });
}

test('builds dropdown options from the url, first child, and registered types', () => {
    const wrapper = mountField({
        meta: {
            showFirstChildOption: true,
            types: {
                entry: { title: 'Entries', component: 'relationship', config: {}, meta: {}, selected: [] },
                asset: { title: 'Assets', component: 'assets', config: {}, meta: {}, selected: [] },
            },
        },
    });

    expect(wrapper.vm.options).toEqual([
        { label: 'None', value: null },
        { label: 'URL', value: 'url' },
        { label: 'First Child', value: 'first-child' },
        { label: 'Entries', value: 'entry' },
        { label: 'Assets', value: 'asset' },
    ]);
});

test('omits the none option when the field is required', () => {
    const wrapper = mountField({ config: { required: true } });

    expect(wrapper.vm.options.some((option) => option.value === null)).toBe(false);
});

test('omits the first child option unless enabled in meta', () => {
    const wrapper = mountField({ meta: { showFirstChildOption: false } });

    expect(wrapper.vm.options.some((option) => option.value === 'first-child')).toBe(false);
});

test('selecting the first child option sets the value to @child', async () => {
    const wrapper = mountField();

    wrapper.vm.option = 'first-child';
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('update:value')[0]).toEqual(['@child']);
});

test('selecting the url option emits the current url value after debouncing', async () => {
    vi.useFakeTimers();

    const wrapper = mountField({ meta: { initialUrl: 'https://example.com' } });

    wrapper.vm.option = 'url';
    await wrapper.vm.$nextTick();
    vi.advanceTimersByTime(200);

    expect(wrapper.emitted('update:value')[0]).toEqual(['https://example.com']);

    vi.useRealTimers();
});

test('selecting a type with an existing selection emits the type reference immediately', async () => {
    const wrapper = mountField({
        meta: {
            types: {
                entry: { title: 'Entries', component: 'relationship', config: {}, meta: {}, selected: ['4'] },
            },
        },
    });

    wrapper.vm.option = 'entry';
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('update:value')[0]).toEqual(['entry::4']);
});

test('selecting a type with no existing selection does not emit a value', async () => {
    // Instead it defers to opening the type selector (openTypeSelector), which relies on
    // a real child fieldtype ref and isn't exercised here since children are stubbed.
    const wrapper = mountField({
        meta: {
            types: {
                entry: { title: 'Entries', component: 'relationship', config: {}, meta: {}, selected: [] },
            },
        },
    });

    wrapper.vm.option = 'entry';
    await wrapper.vm.$nextTick();
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('update:value')).toBeUndefined();
});

test('typeSelected stores the selection and emits the type reference', () => {
    const wrapper = mountField({
        meta: {
            initialOption: 'entry',
            types: {
                entry: { title: 'Entries', component: 'relationship', config: {}, meta: {}, selected: ['1'] },
            },
        },
    });

    wrapper.vm.typeSelected(['42']);

    expect(wrapper.emitted('update:value').at(-1)).toEqual(['entry::42']);
    expect(wrapper.emitted('update:meta').at(-1)[0].types.entry.selected).toEqual(['42']);
});

test('initial selected values are indexed by type', () => {
    const wrapper = mountField({
        meta: {
            types: {
                entry: { title: 'Entries', component: 'relationship', config: {}, meta: {}, selected: ['4'] },
                asset: { title: 'Assets', component: 'assets', config: {}, meta: {}, selected: [] },
            },
        },
    });

    expect(wrapper.vm.selectedByType).toEqual({ entry: ['4'], asset: [] });
});

test('replicatorPreview reflects the url option', () => {
    const wrapper = mountField({
        showFieldPreviews: true,
        meta: { initialOption: 'url', initialUrl: 'https://example.com' },
    });

    expect(wrapper.vm.replicatorPreview).toBe('https://example.com');
});

test('replicatorPreview reflects the first child option', () => {
    const wrapper = mountField({
        showFieldPreviews: true,
        meta: { initialOption: 'first-child' },
    });

    expect(wrapper.vm.replicatorPreview).toBe('First Child');
});

test('replicatorPreview prefers the selected item title for a registered type', () => {
    const wrapper = mountField({
        showFieldPreviews: true,
        meta: {
            initialOption: 'entry',
            types: {
                entry: {
                    title: 'Entries',
                    component: 'relationship',
                    config: {},
                    meta: { data: [{ title: 'About Us' }] },
                    selected: ['4'],
                },
            },
        },
    });

    expect(wrapper.vm.replicatorPreview).toBe('About Us');
});

test('replicatorPreview falls back to the basename when there is no title', () => {
    const wrapper = mountField({
        showFieldPreviews: true,
        meta: {
            initialOption: 'asset',
            types: {
                asset: {
                    title: 'Assets',
                    component: 'assets',
                    config: {},
                    meta: { data: [{ basename: 'photo.jpg' }] },
                    selected: ['4'],
                },
            },
        },
    });

    expect(wrapper.vm.replicatorPreview).toBe('photo.jpg');
});

test('replicatorPreview falls back to the type reference when there is no item data', () => {
    const wrapper = mountField({
        showFieldPreviews: true,
        meta: {
            initialOption: 'entry',
            types: {
                entry: { title: 'Entries', component: 'relationship', config: {}, meta: {}, selected: ['4'] },
            },
        },
    });

    expect(wrapper.vm.replicatorPreview).toBe('entry::4');
});

test('does not request meta for a type that was already preloaded', async () => {
    const post = vi.fn(() => Promise.resolve({ data: { meta: {} } }));

    const wrapper = mountField({
        axiosPost: post,
        meta: {
            initialOption: 'entry',
            types: {
                entry: { title: 'Entries', component: 'relationship', config: {}, meta: { data: [] }, selected: [] },
            },
        },
    });

    await wrapper.vm.$nextTick();

    expect(post).not.toHaveBeenCalled();
});

test('requests meta the first time a type without preloaded meta is picked', async () => {
    const post = vi.fn(() => Promise.resolve({ data: { meta: { data: [] } } }));

    const wrapper = mountField({
        axiosPost: post,
        meta: {
            types: {
                entry: {
                    title: 'Entries',
                    component: 'relationship',
                    config: { type: 'entries', max_items: 1 },
                    meta: null,
                    selected: [],
                },
            },
        },
    });

    wrapper.vm.option = 'entry';
    await flushPromises();

    expect(post).toHaveBeenCalledTimes(1);
    expect(post.mock.calls[0][0]).toBe('/cp/fields/field-meta');
    expect(JSON.parse(atob(post.mock.calls[0][1].config))).toEqual({
        type: 'entries',
        max_items: 1,
        handle: 'entry',
    });
    expect(wrapper.emitted('update:meta').at(-1)[0].types.entry.meta).toEqual({ data: [] });

    wrapper.vm.option = 'url';
    wrapper.vm.option = 'entry';
    await flushPromises();

    expect(post).toHaveBeenCalledTimes(1);
});
