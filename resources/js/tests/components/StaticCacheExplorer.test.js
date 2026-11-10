import { flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h, nextTick } from 'vue';
import axios from 'axios';
import { Button, Select } from '@ui';
import CacheListing from '@/components/utilities/CacheListing.vue';
import Explorer from '@/components/utilities/StaticCacheExplorer.vue';
import * as Globals from '@/bootstrap/globals';
import { dateFormatter, toast } from '@api';

vi.mock('axios', () => ({ default: { post: vi.fn(), get: vi.fn(), isCancel: () => false } }));

const url = 'https://example.com/blog/one';
const refresh = vi.fn();
const ListingStub = defineComponent({
    props: ['url', 'additionalParameters', 'selections', 'allowSelections', 'sortColumn', 'sortDirection'],
    emits: ['update:selections', 'update:searchQuery', 'requestCompleted', 'bulkAction'],
    setup(props, { expose, slots }) {
        expose({ refresh });

        return () => h('div', slots['cell-actions']({ row: { url } }));
    },
});

function mountExplorer() {
    return mount(Explorer, {
        props: {
            url: '/cached-pages',
            invalidateUrl: '/invalidate',
            sites: [{ value: 'en', label: 'English' }, { value: 'fr', label: 'French' }],
        },
        global: {
            stubs: {
                CacheListing: ListingStub,
                Field: { template: '<div><slot /></div>' },
                Select: true,
                Icon: true,
            },
        },
    });
}

beforeEach(() => {
    vi.clearAllMocks();
    vi.spyOn(toast, 'success').mockImplementation(() => {});
    vi.spyOn(toast, 'error').mockImplementation(() => {});
});

afterEach(() => {
    vi.useRealTimers();
    vi.restoreAllMocks();
});

test('passes site and domain filters to the native listing and clears out-of-scope selections', async () => {
    const wrapper = mountExplorer();
    const listing = wrapper.findComponent(CacheListing);

    listing.vm.$emit('requestCompleted', { response: { data: { meta: { domains: ['https://example.com'] } } } });
    listing.vm.$emit('update:selections', [url]);
    await nextTick();

    const [, site, domain] = wrapper.findAllComponents(Select);
    expect(domain.props('options')).toContainEqual({ value: 'https://example.com', label: 'https://example.com' });

    site.vm.$emit('update:modelValue', 'fr');
    domain.vm.$emit('update:modelValue', 'https://example.com');
    await nextTick();

    expect(listing.props('additionalParameters')).toEqual({ site: 'fr', domain: 'https://example.com', status: 'cached' });
    expect(listing.props('selections')).toEqual([]);
    wrapper.unmount();
});

test('invalidates an individual URL once, reports the actual result, and refreshes the list', async () => {
    let resolve;
    axios.post.mockReturnValue(new Promise((done) => resolve = done));
    const wrapper = mountExplorer();
    const rowButton = wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Invalidate');

    await rowButton.trigger('click');
    await rowButton.trigger('click');
    expect(axios.post).toHaveBeenCalledExactlyOnceWith('/invalidate', { urls: [url], site: null, domain: null });
    expect(rowButton.props('disabled')).toBe(true);

    const result = { message: 'Invalidated 2 cached pages in 3ms.', count: 2 };
    resolve({ data: result });
    await flushPromises();

    expect(toast.success).toHaveBeenCalledExactlyOnceWith(result.message);
    expect(wrapper.text()).not.toContain(result.message);
    expect(refresh).toHaveBeenCalledOnce();
    expect(wrapper.emitted('invalidated')).toEqual([[result]]);
    expect(wrapper.emitted('processing')).toEqual([[true], [false]]);
    expect(rowButton.props('disabled')).toBe(false);
    wrapper.unmount();
});

test('browses uncached URLs without offering invalidation or selection', async () => {
    const wrapper = mountExplorer();
    wrapper.findComponent(CacheListing).vm.$emit('update:selections', [url]);
    await nextTick();

    wrapper.findAllComponents(Select)[0].vm.$emit('update:modelValue', 'uncached');
    await nextTick();

    const listing = wrapper.findComponent(CacheListing);
    expect(listing.props('additionalParameters')).toEqual({ site: null, domain: null, status: 'uncached' });
    expect(listing.props('allowSelections')).toBe(false);
    expect(listing.props('sortColumn')).toBe('url');
    expect(listing.props('sortDirection')).toBe('asc');
    expect(listing.props('selections')).toEqual([]);
    expect(wrapper.findAllComponents(Button).some((button) => button.props('text') === 'Invalidate')).toBe(false);

    listing.vm.$emit('bulkAction', [url]);
    expect(axios.post).not.toHaveBeenCalled();
    wrapper.unmount();
});

test('retains selections across pages and invalidates all selected URLs', async () => {
    const selected = [url, 'https://example.de/blog/two'];
    axios.post.mockResolvedValue({ data: { message: 'Invalidated 2 cached pages.', count: 2 } });
    const wrapper = mountExplorer();
    const listing = wrapper.findComponent(CacheListing);

    listing.vm.$emit('update:selections', selected);
    listing.vm.$emit('requestCompleted', { response: { data: { meta: { domains: ['https://example.com', 'https://example.de'] } } } });
    await nextTick();

    expect(listing.props('selections')).toEqual(selected);
    listing.vm.$emit('bulkAction', selected);
    await flushPromises();

    expect(axios.post).toHaveBeenCalledExactlyOnceWith('/invalidate', { urls: selected, site: null, domain: null });
    expect(listing.props('selections')).toEqual([]);
    wrapper.unmount();
});

test('keeps selections after a failed invalidation and allows retrying', async () => {
    axios.post.mockRejectedValue({ response: { data: { errors: { urls: ['Could not invalidate these URLs.'] } } } });
    const wrapper = mountExplorer();
    const listing = wrapper.findComponent(CacheListing);

    listing.vm.$emit('update:selections', [url]);
    await nextTick();
    listing.vm.$emit('bulkAction', [url]);
    await flushPromises();

    expect(toast.error).toHaveBeenCalledExactlyOnceWith('Could not invalidate these URLs.');
    expect(listing.props('selections')).toEqual([url]);
    expect(listing.props('selections')).toEqual([url]);
    expect(refresh).not.toHaveBeenCalled();

    axios.post.mockResolvedValue({ data: { message: 'Invalidated 1 cached page.', count: 1 } });
    listing.vm.$emit('bulkAction', [url]);
    await flushPromises();

    expect(listing.props('selections')).toEqual([]);
    expect(refresh).toHaveBeenCalledOnce();
    wrapper.unmount();
});

test('clears selections when the search changes', async () => {
    const wrapper = mountExplorer();
    const listing = wrapper.findComponent(CacheListing);
    listing.vm.$emit('update:selections', [url]);
    await nextTick();
    listing.vm.$emit('update:searchQuery', 'other');
    await nextTick();

    expect(listing.props('selections')).toEqual([]);
    wrapper.unmount();
});

test('uses the native listing for sorting, column preferences, search, pagination, and filters', async () => {
    vi.spyOn(window, 'scrollTo').mockImplementation(() => {});
    Object.assign(window, Globals);
    window.Statamic = {
        $config: {
            get: (key, fallback) => {
                if (key === 'paginationSizeOptions') return [15, 25, 50, 100];
                if (key === 'paginationSize') return 15;
                return fallback;
            },
        },
        $progress: { loading: vi.fn(), complete: vi.fn() },
        $preferences: { get: () => undefined, set: vi.fn().mockResolvedValue({}) },
        $events: { $on: vi.fn(), $off: vi.fn(), $emit: vi.fn() },
        $toast: { success: vi.fn(), error: vi.fn() },
    };

    const generatedAt = '2026-11-09T12:00:00+00:00';

    axios.get.mockResolvedValue({
        data: {
            data: [{ id: url, title: url, url, generated_at: generatedAt, site_name: 'English' }],
            meta: {
                total: 45,
                current_page: 1,
                last_page: 3,
                per_page: 15,
                from: 1,
                to: 15,
                domains: ['https://example.com'],
                columns: [
                    { field: 'url', label: 'URL', visible: true, sortable: true },
                    { field: 'path', label: 'Path', visible: false, sortable: true },
                    { field: 'generated_at', label: 'Generated', visible: true, sortable: true, fieldtype: 'date' },
                    { field: 'site_name', label: 'Site', visible: true, sortable: true },
                    { field: 'domain', label: 'Domain', visible: false, sortable: true },
                    { field: 'actions', label: 'Actions', visible: true, sortable: false },
                ],
            },
        },
    });

    vi.useFakeTimers();
    vi.setSystemTime(new Date('2026-11-09T14:00:00Z'));
    const wrapper = mount(Explorer, {
        props: {
            url: '/cached-pages',
            invalidateUrl: '/invalidate',
            sites: [{ value: 'en', label: 'English' }, { value: 'fr', label: 'French' }],
        },
        global: {
            stubs: {
                Icon: true,
                Select: true,
                Modal: {
                    props: ['open'],
                    template: '<div v-if="open"><slot /><slot name="footer" /></div>',
                },
                SortableList: { inheritAttrs: false, template: '<slot />' },
                'ui-heading': true,
                'ui-checkbox': true,
                'ui-drag-handle': true,
                'ui-skeleton': true,
                'ui-input-group': { template: '<div><slot /></div>' },
                'ui-input-group-prepend': true,
                'ui-input-group-append': true,
            },
            mocks: { $number: { formatRange: (from, to) => `${from}-${to}`, format: (value) => `${value}` } },
        },
    });
    await flushPromises();

    expect(axios.get.mock.lastCall[1].params).toMatchObject({ sort: 'generated_at', order: 'desc' });
    expect(wrapper.find('th[aria-sort="descending"]').text()).toBe('Generated');
    expect(wrapper.find(`a[href="${url}"]`).exists()).toBe(true);
    const generated = wrapper.find('time');
    expect(generated.text()).toBe('2 hours ago');
    expect(generated.attributes('datetime')).toBe(generatedAt);
    expect(generated.attributes('title')).toBe(dateFormatter.format(generatedAt, {
        preset: 'datetime',
        second: '2-digit',
        timeZoneName: 'short',
    }));

    const generatedHeader = wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Generated');
    await generatedHeader.trigger('click');
    await flushPromises();
    expect(axios.get.mock.lastCall[1].params).toMatchObject({ sort: 'generated_at', order: 'asc' });
    expect(wrapper.find('th[aria-sort="ascending"]').text()).toBe('Generated');

    await wrapper.find('[aria-label="Customize Columns"]').trigger('click');
    expect(wrapper.find('ui-checkbox-stub[label="Path"]').exists()).toBe(true);
    expect(wrapper.find('ui-checkbox-stub[label="Domain"]').exists()).toBe(true);

    const saveColumns = wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Save');
    await saveColumns.trigger('click');
    await flushPromises();
    expect(Statamic.$preferences.set).toHaveBeenCalledExactlyOnceWith('cache.static_pages.columns', [
        'url', 'generated_at', 'site_name', 'actions',
    ]);

    await wrapper.find('input').setValue('blog');
    await vi.advanceTimersByTimeAsync(300);
    expect(axios.get.mock.lastCall[1].params.search).toBe('blog');

    const next = wrapper.findAllComponents(Button).find((button) => button.props('icon') === 'chevron-right');
    await next.trigger('click');
    await flushPromises();
    expect(axios.get.mock.lastCall[1].params.page).toBe(2);

    wrapper.findAllComponents(Select)[1].vm.$emit('update:modelValue', 'fr');
    await nextTick();
    expect(axios.get.mock.lastCall[1].params.site).toBe('fr');

    wrapper.unmount();
    vi.useRealTimers();
});
