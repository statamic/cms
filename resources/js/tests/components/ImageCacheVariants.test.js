import { enableAutoUnmount, flushPromises, mount } from '@vue/test-utils';
import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import axios from 'axios';
import { Button } from '@ui';
import CacheListing from '@/components/utilities/CacheListing.vue';
import Variants from '@/components/utilities/ImageCacheVariants.vue';
import { __ } from '@/bootstrap/globals';
import { dateFormatter, toast } from '@api';

vi.mock('axios', () => ({ default: { post: vi.fn() } }));

enableAutoUnmount(afterEach);
const refresh = vi.fn();

beforeEach(() => {
    vi.clearAllMocks();
    vi.spyOn(toast, 'success').mockImplementation(() => {});
    vi.spyOn(toast, 'error').mockImplementation(() => {});
});

afterEach(() => vi.restoreAllMocks());

const variant = {
    id: 'containers/blog/photo.jpg/hash/photo.webp',
    title: 'photo.webp',
    hash: 'hash',
    format: 'WEBP',
    size: 1024,
    size_formatted: '1 KB',
    generated_at: '2026-11-09T12:00:00+00:00',
    preview_url: '/cached-image',
};

function mountVariants(previewVisible = true) {
    const ListingStub = defineComponent({
        props: ['url', 'additionalParameters', 'preferencesPrefix', 'selections'],
        emits: ['bulkAction', 'update:selections'],
        setup(props, { expose, slots }) {
            expose({ refresh });

            return () => h('div', [
                slots['panel-actions']?.(),
                previewVisible ? slots['cell-preview']({ row: variant }) : null,
                slots['cell-dimensions']({ row: variant, isColumnVisible: () => previewVisible }),
                slots['cell-size']({ row: variant }),
                slots['cell-generated_at']({ row: variant }),
                slots['cell-actions']({ row: variant }),
            ]);
        },
    });

    return mount(Variants, {
        props: {
            container: { id: 'blog', title: 'Editorial' },
            asset: { id: 'blog::photo.jpg', path: 'photo.jpg' },
            url: '/image-variants',
            clearUrl: '/clear-image-variant',
        },
        global: {
            stubs: { CacheListing: ListingStub, Icon: true, FileIcon: true },
            mocks: { __ },
        },
    });
}

test('shows existing previews, measured dimensions, sizes, and relative generation dates', async () => {
    const wrapper = mountVariants();
    const listing = wrapper.findComponent(CacheListing);

    expect(listing.props('additionalParameters')).toEqual({ container: 'blog', asset: 'blog::photo.jpg' });
    expect(listing.props('preferencesPrefix')).toBe('cache.image_variants');
    expect(wrapper.find('a').attributes()).toMatchObject({ href: '/cached-image', target: '_blank', rel: 'noopener noreferrer' });
    expect(wrapper.text()).toContain('1 KB');

    const image = wrapper.find('img');
    Object.defineProperties(image.element, {
        naturalWidth: { value: 640 },
        naturalHeight: { value: 480 },
    });
    await image.trigger('load');

    expect(wrapper.text()).toContain('640 × 480');
    expect(wrapper.find('time').text()).toBe(dateFormatter.format(variant.generated_at, { relative: true }));
    expect(wrapper.find('time').attributes('title')).toBe(dateFormatter.format(variant.generated_at, {
        preset: 'datetime',
        second: '2-digit',
        timeZoneName: 'short',
    }));
});

test('can determine dimensions when the preview column is hidden', async () => {
    const wrapper = mountVariants(false);
    const image = wrapper.find('img');
    expect(image.classes()).toContain('sr-only');

    Object.defineProperties(image.element, {
        naturalWidth: { value: 320 },
        naturalHeight: { value: 240 },
    });
    await image.trigger('load');

    expect(wrapper.text()).toContain('320 × 240');
});

test('handles missing previews and blocks clear actions during an operation', async () => {
    const wrapper = mountVariants();
    await wrapper.find('img').trigger('error');

    expect(wrapper.find('img').exists()).toBe(false);
    expect(wrapper.find('file-icon-stub').exists()).toBe(true);

    const clear = wrapper.findAllComponents(Button).find((button) => button.props('text') === 'Clear');
    await wrapper.setProps({ processing: true });

    expect(wrapper.findAllComponents(Button).every((button) => button.props('disabled'))).toBe(true);
    await clear.trigger('click');
    expect(axios.post).not.toHaveBeenCalled();
});

test('clears only the chosen variation once, then refreshes and reports updated totals', async () => {
    let resolve;
    axios.post.mockReturnValue(new Promise((done) => resolve = done));
    const wrapper = mountVariants();
    const clear = wrapper.findAllComponents(Button).find((button) => button.attributes('aria-label') === 'Clear cached variation hash');

    await clear.trigger('click');
    await clear.trigger('click');
    expect(axios.post).toHaveBeenCalledExactlyOnceWith('/clear-image-variant', {
        container: 'blog',
        asset: 'blog::photo.jpg',
        variant: variant.id,
    });
    expect(clear.props('loading')).toBe(true);
    expect(wrapper.findAllComponents(Button).every((button) => button.props('disabled'))).toBe(true);

    const result = { count: 1, size: 1024, remaining: 1, remaining_size: 512, message: 'Variation cleared.' };
    resolve({ data: result });
    await flushPromises();

    expect(toast.success).toHaveBeenCalledExactlyOnceWith(result.message);
    expect(wrapper.text()).not.toContain(result.message);
    expect(refresh).toHaveBeenCalledOnce();
    expect(wrapper.emitted('cleared')).toEqual([[result]]);
    expect(wrapper.emitted('processing')).toEqual([[true], [false]]);
});

test('allows retrying a failed clear and avoids reloading the variation list after clearing its final row', async () => {
    axios.post.mockRejectedValue({ response: { data: { errors: { variant: ['Could not clear this variation.'] } } } });
    const wrapper = mountVariants();
    const clear = wrapper.findAllComponents(Button).find((button) => button.attributes('aria-label') === 'Clear cached variation hash');

    await clear.trigger('click');
    await flushPromises();
    expect(toast.error).toHaveBeenCalledExactlyOnceWith('Could not clear this variation.');
    expect(wrapper.emitted('cleared')).toBeUndefined();
    expect(clear.props('disabled')).toBe(false);

    const result = { count: 1, size: 1024, remaining: 0, remaining_size: 0, message: 'Variation cleared.' };
    axios.post.mockResolvedValue({ data: result });
    await clear.trigger('click');
    await flushPromises();

    expect(wrapper.emitted('cleared')).toEqual([[result]]);
    expect(refresh).not.toHaveBeenCalled();
});

test('bulk clears selected variations and preserves the selection after a failure', async () => {
    axios.post.mockRejectedValue({ response: { data: { errors: { variants: ['Unable to clear these variations.'] } } } });
    const wrapper = mountVariants();
    const listing = wrapper.findComponent(CacheListing);
    const selected = [variant.id, 'containers/blog/photo.jpg/other/photo.jpg'];
    listing.vm.$emit('update:selections', selected);
    listing.vm.$emit('bulkAction', selected);
    await flushPromises();

    expect(axios.post.mock.lastCall).toEqual(['/clear-image-variant', {
        container: 'blog',
        asset: 'blog::photo.jpg',
        variants: selected,
    }]);
    expect(listing.props('selections')).toEqual(selected);
    expect(refresh).not.toHaveBeenCalled();

    axios.post.mockResolvedValue({ data: { count: 2, size: 2048, remaining: 1, remaining_size: 512, message: 'Cleared.' } });
    listing.vm.$emit('bulkAction', selected);
    await flushPromises();

    expect(listing.props('selections')).toEqual([]);
    expect(refresh).toHaveBeenCalledOnce();
});
