import { enableAutoUnmount, flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import axios from 'axios';
import { Button } from '@ui';
import CacheListing from '@/components/utilities/CacheListing.vue';
import Explorer from '@/components/utilities/ImageCacheExplorer.vue';
import ImageCacheAssets from '@/components/utilities/ImageCacheAssets.vue';
import ImageCacheBreadcrumbs from '@/components/utilities/ImageCacheBreadcrumbs.vue';
import ImageCacheVariants from '@/components/utilities/ImageCacheVariants.vue';
import * as Globals from '@/bootstrap/globals';
import { toast } from '@api';

vi.mock('axios', () => ({ default: { post: vi.fn(), get: vi.fn(), isCancel: () => false } }));

enableAutoUnmount(afterEach);

const refresh = vi.fn();
const containers = [
    { id: 'blog', handle: 'blog', title: 'Editorial', count: 2, size: 10, size_formatted: '10 B' },
    { id: 'empty', handle: 'empty', title: 'Empty', count: 0, size: 0, size_formatted: '0 B' },
];
const cachedAsset = {
    id: 'blog::nested/photo.jpg',
    path: 'nested/photo.jpg',
    title: 'photo.jpg',
    count: 2,
    size: 10,
    size_formatted: '10 B',
    thumbnail: '/cached-image',
    extension: 'jpg',
};
const ListingStub = defineComponent({
    props: ['additionalParameters', 'selections'],
    emits: ['requestCompleted', 'bulkAction', 'update:selections'],
    setup(props, { expose, slots }) {
        expose({ refresh });

        return () => {
            const rows = props.additionalParameters?.container ? [cachedAsset] : containers;

            return h('div', [
                slots.breadcrumbs?.(),
                ...rows.flatMap((row) => [
                    slots['cell-title']?.({ row }),
                    slots['cell-path']?.({ row }),
                    slots['cell-count']({ row }),
                    slots['cell-size']({ row }),
                    slots['cell-actions']({ row }),
                ]),
            ]);
        };
    },
});

function mountExplorer() {
    return mount(Explorer, {
        props: {
            url: '/image-containers',
            clearUrl: '/clear-image-container',
            assetsUrl: '/image-assets',
            clearAssetUrl: '/clear-image-asset',
            variantsUrl: '/image-variants',
            clearVariantUrl: '/clear-image-variant',
        },
        global: {
            stubs: { CacheListing: ListingStub, ImageCacheAssets: true, Icon: true, 'file-icon': true },
            mocks: { __: Globals.__ },
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

test('renders counts and sizes and disables clearing empty containers', () => {
    const wrapper = mountExplorer();
    const buttons = wrapper.findAllComponents(Button).filter((button) => button.props('text') === 'Clear');

    expect(wrapper.text()).toContain('10 B');
    expect(buttons[0].attributes('aria-label')).toBe('Clear cached images in Editorial');
    expect(buttons[0].props('disabled')).toBe(false);
    expect(buttons[1].props('disabled')).toBe(true);
});

test('clears the chosen container once, shows a toast, and refreshes the listing', async () => {
    let resolve;
    axios.post.mockReturnValue(new Promise((done) => resolve = done));
    const wrapper = mountExplorer();
    const button = wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Clear');

    await button.trigger('click');
    await button.trigger('click');

    expect(axios.post).toHaveBeenCalledExactlyOnceWith('/clear-image-container', { container: 'blog' });
    expect(button.props('loading')).toBe(true);
    expect(wrapper.findAllComponents(Button).every((button) => button.props('disabled'))).toBe(true);

    const result = { count: 2, size: 10, message: 'Cleared 2 cached images (10 B) from Editorial in 3ms.' };
    resolve({ data: result });
    await flushPromises();

    expect(toast.success).toHaveBeenCalledExactlyOnceWith(result.message);
    expect(wrapper.text()).not.toContain(result.message);
    expect(refresh).toHaveBeenCalledOnce();
    expect(wrapper.emitted('cleared')).toEqual([[result]]);
    expect(wrapper.emitted('processing')).toEqual([[true], [false]]);
    expect(button.props('disabled')).toBe(false);
});

test('shows errors and allows retrying without claiming a successful clear', async () => {
    axios.post.mockRejectedValue({ response: { data: { errors: { container: ['Could not clear this container.'] } } } });
    const wrapper = mountExplorer();
    const button = wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Clear');

    await button.trigger('click');
    await flushPromises();

    expect(toast.error).toHaveBeenCalledExactlyOnceWith('Could not clear this container.');
    expect(button.props('disabled')).toBe(false);
    expect(refresh).not.toHaveBeenCalled();
    expect(wrapper.emitted('cleared')).toBeUndefined();

    axios.post.mockResolvedValue({ data: { count: 2, size: 10, message: 'Cleared.' } });
    await button.trigger('click');
    await flushPromises();

    expect(refresh).toHaveBeenCalledOnce();
    expect(wrapper.emitted('cleared')).toHaveLength(1);
});

test('bulk clears selected containers and assets and resets their selections', async () => {
    const result = { count: 2, size: 10, message: 'Cleared.' };
    axios.post.mockResolvedValue({ data: result });

    const containers = mountExplorer();
    const containerListing = containers.findComponent(CacheListing);
    const containerIds = ['blog', 'empty'];
    containerListing.vm.$emit('update:selections', containerIds);
    containerListing.vm.$emit('bulkAction', containerIds);
    await flushPromises();

    expect(axios.post.mock.lastCall).toEqual(['/clear-image-container', { containers: containerIds }]);
    expect(containerListing.props('selections')).toEqual([]);

    const assets = mount(ImageCacheAssets, {
        props: {
            container: { id: 'blog', title: 'Editorial' },
            url: '/image-assets',
            clearUrl: '/clear-image-asset',
            variantsUrl: '/image-variants',
            clearVariantUrl: '/clear-image-variant',
        },
        global: {
            stubs: { CacheListing: ListingStub, Icon: true, 'file-icon': true },
            mocks: { __: Globals.__ },
        },
    });
    const assetListing = assets.findComponent(CacheListing);
    const assetIds = [cachedAsset.id, 'blog::other.jpg'];
    assetListing.vm.$emit('update:selections', assetIds);
    assetListing.vm.$emit('bulkAction', assetIds);
    await flushPromises();

    expect(axios.post.mock.lastCall).toEqual(['/clear-image-asset', { container: 'blog', assets: assetIds }]);
    expect(assetListing.props('selections')).toEqual([]);
    expect(assets.emitted('cleared')).toEqual([[result]]);
});

test('drills into a container, refreshes its totals after clearing, and preserves the container listing', async () => {
    const wrapper = mountExplorer();
    const listing = wrapper.findComponent(CacheListing);

    listing.vm.$emit('requestCompleted', { response: { data: { meta: { container_count: 2, single_container: null } } } });
    await wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Editorial').trigger('click');

    const assets = wrapper.findComponent(ImageCacheAssets);
    expect(assets.props()).toMatchObject({
        container: containers[0],
        url: '/image-assets',
        clearUrl: '/clear-image-asset',
        variantsUrl: '/image-variants',
        clearVariantUrl: '/clear-image-variant',
    });
    expect(listing.element.style.display).toBe('none');

    assets.vm.$emit('processing', true);
    expect(wrapper.emitted('processing')).toEqual([[true]]);

    const result = { count: 1, size: 10, message: 'Cleared.' };
    assets.vm.$emit('cleared', result);
    expect(refresh).toHaveBeenCalledOnce();
    expect(wrapper.emitted('cleared')).toEqual([[result]]);

    assets.vm.$emit('processing', false);
    await flushPromises();
    await wrapper.findComponent(ImageCacheBreadcrumbs).findAllComponents(Button)[0].trigger('click');
    await flushPromises();
    expect(wrapper.findComponent(ImageCacheAssets).exists()).toBe(false);
    expect(listing.element.style.display).toBe('');
});

test('opens the only container automatically without mistaking a filtered result for a single container', async () => {
    const wrapper = mountExplorer();
    const listing = wrapper.findComponent(CacheListing);

    listing.vm.$emit('requestCompleted', { response: { data: { meta: { container_count: 3, single_container: null } } } });
    await flushPromises();
    expect(wrapper.findComponent(ImageCacheAssets).exists()).toBe(false);

    listing.vm.$emit('requestCompleted', { response: { data: { meta: { container_count: 1, single_container: containers[0] } } } });
    await flushPromises();

    expect(wrapper.findComponent(ImageCacheAssets).props('container')).toEqual(containers[0]);
    expect(wrapper.findComponent(ImageCacheBreadcrumbs).props('showContainers')).toBe(false);
    expect(listing.element.style.display).toBe('none');
});

test('shows the breadcrumb hierarchy across levels and disables its navigation while clearing', async () => {
    const wrapper = mountExplorer();
    wrapper.findComponent(CacheListing).vm.$emit('requestCompleted', {
        response: { data: { meta: { container_count: 2, single_container: null } } },
    });
    await flushPromises();

    const breadcrumbs = wrapper.findComponent(ImageCacheBreadcrumbs);
    await wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Editorial').trigger('click');

    const assets = wrapper.findComponent(ImageCacheAssets);
    assets.vm.$emit('update:selectedAsset', cachedAsset);
    await flushPromises();

    expect(breadcrumbs.findAllComponents(Button).map((button) => button.props('text'))).toEqual([
        'All containers', 'Editorial', 'photo.jpg',
    ]);

    assets.vm.$emit('processing', true);
    await flushPromises();
    expect(breadcrumbs.findAllComponents(Button).every((button) => button.props('disabled'))).toBe(true);

    assets.vm.$emit('processing', false);
    await flushPromises();
    await breadcrumbs.findAllComponents(Button)[1].trigger('click');
    expect(assets.props('selectedAsset')).toBe(null);
});

test('keeps the single-container view focused and allows retrying a failed asset clear', async () => {
    axios.post.mockRejectedValue({ response: { data: { errors: { asset: ['Could not clear this asset.'] } } } });
    const wrapper = mount(ImageCacheAssets, {
        props: {
            container: containers[0],
            url: '/image-assets',
            clearUrl: '/clear-image-asset',
            variantsUrl: '/image-variants',
            clearVariantUrl: '/clear-image-variant',
        },
        global: {
            stubs: { CacheListing: ListingStub, Icon: true, 'file-icon': true },
            mocks: { __: Globals.__ },
        },
    });

    expect(wrapper.text()).not.toContain('All containers');
    expect(wrapper.text()).toContain(cachedAsset.path);

    const button = wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Clear');
    await button.trigger('click');
    await flushPromises();

    expect(toast.error).toHaveBeenCalledExactlyOnceWith('Could not clear this asset.');
    expect(refresh).not.toHaveBeenCalled();
    expect(wrapper.emitted('cleared')).toBeUndefined();
    expect(button.props('disabled')).toBe(false);

    axios.post.mockResolvedValue({ data: { count: 2, size: 10, message: 'Cleared.' } });
    await button.trigger('click');
    await flushPromises();

    expect(axios.post.mock.lastCall).toEqual(['/clear-image-asset', { container: 'blog', asset: cachedAsset.id }]);
    expect(refresh).toHaveBeenCalledOnce();
    expect(wrapper.emitted('cleared')).toHaveLength(1);
});

test('shows asset thumbnails and opens their cached variations', async () => {
    const wrapper = mount(ImageCacheAssets, {
        props: {
            container: containers[0],
            url: '/image-assets',
            clearUrl: '/clear-image-asset',
            variantsUrl: '/image-variants',
            clearVariantUrl: '/clear-image-variant',
        },
        global: {
            stubs: { CacheListing: ListingStub, ImageCacheVariants: true, Icon: true, 'file-icon': true },
            mocks: { __: Globals.__ },
        },
    });

    expect(wrapper.find('img').attributes('src')).toBe('/cached-image');
    expect(wrapper.find('img').attributes('alt')).toBe('');

    const openAsset = () => wrapper.findAllComponents(Button).find((button) => button.props('text') === cachedAsset.path);
    await openAsset().trigger('click');
    const variants = wrapper.findComponent(ImageCacheVariants);

    expect(variants.props()).toMatchObject({ asset: cachedAsset, container: containers[0], url: '/image-variants' });
    expect(wrapper.findComponent(CacheListing).element.style.display).toBe('none');


});

test('refreshes asset totals after a variation clear and leaves the detail view only when the final variation is gone', async () => {
    const wrapper = mount(ImageCacheAssets, {
        props: {
            container: containers[0],
            selectedAsset: cachedAsset,
            url: '/image-assets',
            clearUrl: '/clear-image-asset',
            variantsUrl: '/image-variants',
            clearVariantUrl: '/clear-image-variant',
        },
        global: {
            stubs: { CacheListing: ListingStub, ImageCacheVariants: true, Icon: true, 'file-icon': true },
            mocks: { __: Globals.__ },
        },
    });

    wrapper.findComponent(ImageCacheVariants).vm.$emit('cleared', { count: 1, remaining: 1, remaining_size: 512 });
    await flushPromises();
    expect(wrapper.findComponent(ImageCacheVariants).props('asset').count).toBe(1);
    expect(refresh).toHaveBeenCalledOnce();

    wrapper.findComponent(ImageCacheVariants).vm.$emit('cleared', { count: 1, remaining: 0, remaining_size: 0 });
    await flushPromises();
    expect(wrapper.findComponent(ImageCacheVariants).exists()).toBe(false);
    expect(wrapper.findComponent(CacheListing).element.style.display).toBe('');
    expect(refresh).toHaveBeenCalledTimes(2);
    expect(wrapper.emitted('cleared')).toHaveLength(2);
});

test('uses native listings to search, sort, drill into assets, clear one, and return to updated container totals', async () => {
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
        $preferences: { get: () => undefined },
        $events: { $on: vi.fn(), $off: vi.fn(), $emit: vi.fn() },
        $toast: { error: vi.fn() },
    };

    const containerResponse = {
        data: {
            data: containers,
            meta: {
                total: 2,
                current_page: 1,
                last_page: 1,
                per_page: 15,
                from: 1,
                to: 2,
                container_count: 2,
                single_container: null,
                columns: [
                    { field: 'title', label: 'Container', visible: true, sortable: true },
                    { field: 'handle', label: 'Handle', visible: false, sortable: true },
                    { field: 'count', label: 'Cached images', visible: true, sortable: true, numeric: true },
                    { field: 'size', label: 'Size', visible: true, sortable: true, numeric: true },
                    { field: 'actions', label: 'Actions', visible: true, sortable: false },
                ],
            },
        },
    };
    axios.get.mockResolvedValue(containerResponse);

    vi.useFakeTimers();
    const wrapper = mount(Explorer, {
        props: {
            url: '/image-containers',
            clearUrl: '/clear-image-container',
            assetsUrl: '/image-assets',
            clearAssetUrl: '/clear-image-asset',
            variantsUrl: '/image-variants',
            clearVariantUrl: '/clear-image-variant',
        },
        global: {
            stubs: {
                Icon: true, 'file-icon': true,
                SortableList: { inheritAttrs: false, template: '<slot />' },
                'ui-heading': true,
                'ui-checkbox': true,
                'ui-drag-handle': true,
                'ui-skeleton': true,
                'ui-input-group': { template: '<div><slot /></div>' },
                'ui-input-group-prepend': true,
                'ui-input-group-append': true,
            },
            mocks: {
                __: Globals.__,
                $number: { formatRange: (from, to) => `${from}-${to}`, format: (value) => `${value}` },
            },
        },
    });
    await flushPromises();

    expect(axios.get.mock.lastCall[1].params).toMatchObject({ sort: 'size', order: 'desc' });
    expect(wrapper.text()).toContain('Editorial');
    expect(wrapper.text()).toContain('10 B');
    expect(wrapper.find('th[aria-sort="descending"]').text()).toBe('Size');
    expect(wrapper.findAll('tbody [role="checkbox"]')).toHaveLength(2);
    expect(wrapper.find('[aria-label="Customize Columns"]').exists()).toBe(true);

    const countHeader = wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Cached images');
    await countHeader.trigger('click');
    await flushPromises();
    expect(axios.get.mock.lastCall[1].params).toMatchObject({ sort: 'count', order: 'asc' });

    await wrapper.find('input').setValue('Editorial');
    await vi.advanceTimersByTimeAsync(300);
    expect(axios.get.mock.lastCall[1].params.search).toBe('Editorial');

    let cleared = false;
    axios.get.mockImplementation(async (url) => {
        if (url === '/image-assets') {
            return {
                data: {
                    data: cleared ? [] : [cachedAsset],
                    meta: {
                        total: cleared ? 0 : 1,
                        current_page: 1,
                        last_page: 1,
                        per_page: 15,
                        from: cleared ? null : 1,
                        to: cleared ? null : 1,
                        columns: [
                            { field: 'path', label: 'Asset', visible: true, sortable: true },
                            ...containerResponse.data.meta.columns.slice(2),
                        ],
                    },
                },
            };
        }

        const rows = containers.map((row) => {
            if (cleared && row.id === 'blog') {
                return { ...row, count: 0, size: 0, size_formatted: '0 B' };
            }

            return row;
        });

        return { data: { ...containerResponse.data, data: rows } };
    });

    await wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Editorial').trigger('click');
    await flushPromises();

    const assets = wrapper.findComponent(ImageCacheAssets);
    expect(assets.text()).toContain('nested/photo.jpg');
    expect(assets.text()).toContain('10 B');
    expect(axios.get.mock.calls.some(([url, options]) => url === '/image-assets'
        && options.params.container === 'blog' && options.params.sort === 'size')).toBe(true);

    let resolve;
    axios.post.mockReturnValue(new Promise((done) => resolve = done));
    const clear = assets.findAllComponents(Button).find((button) => button.props('text') === 'Clear');
    const back = wrapper.findComponent(ImageCacheBreadcrumbs).findAllComponents(Button).find((button) => button.props('text') === 'All containers');
    await clear.trigger('click');
    await clear.trigger('click');

    expect(axios.post).toHaveBeenCalledExactlyOnceWith('/clear-image-asset', { container: 'blog', asset: cachedAsset.id });
    expect(back.props('disabled')).toBe(true);
    expect(clear.props('loading')).toBe(true);

    const result = { count: 2, size: 10, message: 'Cleared 2 cached images for nested/photo.jpg.' };
    cleared = true;
    resolve({ data: result });
    await flushPromises();

    expect(toast.success).toHaveBeenCalledExactlyOnceWith(result.message);
    expect(assets.text()).not.toContain(cachedAsset.path);
    expect(wrapper.emitted('cleared')).toEqual([[result]]);
    expect(wrapper.emitted('processing')).toEqual([[true], [false]]);

    await back.trigger('click');
    await flushPromises();
    expect(wrapper.findComponent(ImageCacheAssets).exists()).toBe(false);
    expect(wrapper.find('input').element.value).toBe('Editorial');
    expect(wrapper.find('[aria-label="Clear cached images in Editorial"]').attributes('disabled')).toBeDefined();
});
