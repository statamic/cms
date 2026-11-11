import { enableAutoUnmount, shallowMount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { nextTick } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import Cache from '@/pages/utilities/Cache.vue';
import { toast } from '@api';
import { Panel, Stack } from '@ui';
import ImageCacheExplorer from '@/components/utilities/ImageCacheExplorer.vue';
import StaticCacheExplorer from '@/components/utilities/StaticCacheExplorer.vue';

vi.mock('axios', () => ({ default: { post: vi.fn() } }));
vi.mock('@inertiajs/vue3', async (importOriginal) => ({
    ...await importOriginal(),
    router: { post: vi.fn(), reload: vi.fn() },
}));

enableAutoUnmount(afterEach);

function mountCache() {
    return shallowMount(Cache, {
        props: {
            stache: { records: 2, size: '1 KB', time: '2ms', rebuilt: 'just now' },
            cache: { driver: 'file' },
            static: { enabled: true, strategy: 'half', count: 2 },
            staticCoverage: { cached: 2, total: 3, uncached: 1 },
            images: { count: 4, assets: 2, size: '1 KB' },
            cacheSites: [{ value: 'en', label: 'English' }, { value: 'fr', label: 'French' }],
            previewStaticPagesUrl: '/preview',
            invalidatePagesUrl: '/invalidate',
            cacheStaticPageUrl: '/cache-page',
            warmStaticUrl: '/warm',
            imageContainersUrl: '/image-containers',
            clearImageContainerUrl: '/clear-image-container',
            imageAssetsUrl: '/image-assets',
            clearImageAssetUrl: '/clear-image-asset',
            imageVariantsUrl: '/image-variants',
            clearImageVariantUrl: '/clear-image-variant',
        },
        global: {
            renderStubDefaultSlot: true,
            mocks: { __n: (key) => key },
            stubs: {
                Head: true,
                Deferred: { template: '<div><slot /></div>' },
                CommandPaletteItem: { template: '<div><slot /></div>' },
            },
        },
    });
}

beforeEach(() => {
    vi.resetAllMocks();
    vi.useFakeTimers();
    vi.spyOn(toast, 'error').mockImplementation(() => {});
});

afterEach(() => {
    vi.useRealTimers();
    vi.restoreAllMocks();
});

test('opens the image explorer, protects an in-progress clear, and reloads image totals', async () => {
    const wrapper = mountCache();
    expect(wrapper.findComponent(ImageCacheExplorer).exists()).toBe(false);

    await wrapper.find('[aria-label="Browse cached images"]').trigger('click');
    const explorer = wrapper.findComponent(ImageCacheExplorer);
    const stack = wrapper.findAllComponents(Stack).find((stack) => stack.props('title') === 'Cached images');

    expect(explorer.props()).toMatchObject({
        url: '/image-containers',
        clearUrl: '/clear-image-container',
        assetsUrl: '/image-assets',
        clearAssetUrl: '/clear-image-asset',
        variantsUrl: '/image-variants',
        clearVariantUrl: '/clear-image-variant',
    });
    expect(stack.props('open')).toBe(true);

    explorer.vm.$emit('processing', true);
    await nextTick();
    expect(stack.props('beforeClose')()).toBe(false);

    explorer.vm.$emit('cleared', { count: 2, size: 10 });
    expect(router.reload).toHaveBeenCalledExactlyOnceWith({ only: ['images'], preserveScroll: true });

    explorer.vm.$emit('processing', false);
    await nextTick();
    expect(stack.props('beforeClose')()).toBe(true);
});

test('shows image variations and asset totals and browses cached or uncached static pages', async () => {
    const wrapper = mountCache();
    const imagePanel = wrapper.findAllComponents(Panel).find((panel) => panel.text().includes('Image Cache'));
    const imageMetrics = imagePanel.find('dl');
    expect(imageMetrics.findAll('dt').map((label) => label.text())).toEqual(['Variations', 'Assets', 'Size']);
    expect(imageMetrics.findAll('dd').map((value) => value.text())).toEqual(['4', '2', '1 KB']);

    expect(wrapper.find('[text="Browse uncached"]').exists()).toBe(false);
    await wrapper.find('[text="Uncached pages"]').trigger('click');
    const explorer = wrapper.findComponent(StaticCacheExplorer);
    expect(explorer.props('status')).toBe('uncached');
    expect(explorer.props('cacheUrl')).toBe('/cache-page');
    expect(wrapper.findAllComponents(Stack).some((stack) => stack.props('title') === 'Static Page Cache')).toBe(true);

    explorer.vm.$emit('invalidated');
    expect(router.reload).toHaveBeenCalledExactlyOnceWith({ only: ['static', 'staticCoverage'], preserveScroll: true });
    router.reload.mockClear();
    explorer.vm.$emit('cached');
    expect(router.reload).toHaveBeenCalledExactlyOnceWith({ only: ['static', 'staticCoverage'], preserveScroll: true });

    await wrapper.find('[text="Cached pages"]').trigger('click');
    expect(explorer.props('status')).toBe('cached');
});

test('debounces pattern previews and includes the selected site', async () => {
    axios.post.mockResolvedValue({ data: { total: 2, data: [{ url: 'https://example.com/fr/blog/one' }] } });
    const wrapper = mountCache();
    wrapper.vm.openInvalidateStaticUrls();
    wrapper.vm.staticUrls = '/about';
    wrapper.vm.invalidationSite = 'fr';
    await nextTick();
    wrapper.vm.staticUrls = '/blog/*\n\n';
    await nextTick();

    await vi.advanceTimersByTimeAsync(299);
    expect(axios.post).not.toHaveBeenCalled();
    expect(wrapper.vm.previewLoading).toBe(true);

    await vi.advanceTimersByTimeAsync(1);
    expect(axios.post).toHaveBeenCalledExactlyOnceWith('/preview', { urls: ['/blog/*'], site: 'fr' }, {
        signal: expect.any(AbortSignal),
    });
    expect(wrapper.vm.preview.total).toBe(2);
    expect(wrapper.vm.previewLoading).toBe(false);
});

test('cancels obsolete previews and ignores responses for old patterns', async () => {
    let resolveOld;
    axios.post.mockReturnValueOnce(new Promise((resolve) => resolveOld = resolve));
    const wrapper = mountCache();
    wrapper.vm.openInvalidateStaticUrls();
    wrapper.vm.staticUrls = '/blog/*';
    await nextTick();
    await vi.advanceTimersByTimeAsync(300);
    const oldSignal = axios.post.mock.calls[0][2].signal;

    axios.post.mockResolvedValueOnce({ data: { total: 0, data: [] } });
    wrapper.vm.staticUrls = '/missing';
    await nextTick();
    expect(oldSignal.aborted).toBe(true);
    await vi.advanceTimersByTimeAsync(300);
    resolveOld({ data: { total: 99, data: [] } });
    await Promise.resolve();

    expect(wrapper.vm.preview.total).toBe(0);
    expect(wrapper.vm.previewLoading).toBe(false);
    wrapper.vm.invalidateStaticUrls();
    expect(router.post).not.toHaveBeenCalled();
});

test('shows validation errors and recovers when the input is corrected', async () => {
    axios.post.mockRejectedValueOnce({ response: { data: { errors: { 'urls.0': ['Use a trailing wildcard.'] } } } });
    const wrapper = mountCache();
    wrapper.vm.openInvalidateStaticUrls();
    wrapper.vm.staticUrls = '/bl*g/*';
    await nextTick();
    await vi.advanceTimersByTimeAsync(300);

    expect(wrapper.vm.previewError).toBe('Use a trailing wildcard.');
    wrapper.vm.invalidateStaticUrls();
    expect(router.post).not.toHaveBeenCalled();

    axios.post.mockResolvedValueOnce({ data: { total: 1, data: [{ url: 'https://example.com/blog/one' }] } });
    wrapper.vm.staticUrls = '/blog/*';
    await nextTick();
    expect(wrapper.vm.previewError).toBeNull();
    await vi.advanceTimersByTimeAsync(300);
    wrapper.vm.invalidateStaticUrls();

    expect(router.post).toHaveBeenCalledExactlyOnceWith('/invalidate', { urls: ['/blog/*'], site: null }, expect.any(Object));
    const options = router.post.mock.calls[0][2];
    options.onSuccess();
    options.onFinish();
    await nextTick();

    expect(wrapper.vm.invalidateStaticUrlsModal).toBe(false);
    expect(wrapper.text()).not.toContain('Cleared 1 cached page in 2ms.');
    expect(wrapper.vm.activeAction).toBeNull();
});

test('exposes static warming with loading and repeat-click protection', async () => {
    const wrapper = mountCache();
    wrapper.vm.warmStatic();
    wrapper.vm.warmStatic();
    await nextTick();

    expect(router.post).toHaveBeenCalledExactlyOnceWith('/warm', {}, expect.any(Object));
    expect(wrapper.vm.activeAction).toBe('warmStatic');

    const options = router.post.mock.calls[0][2];
    options.onFinish();
    await nextTick();

    expect(wrapper.vm.activeAction).toBeNull();
    expect(wrapper.find('[variant="success"]').exists()).toBe(false);
});
