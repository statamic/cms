import { flushPromises, mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import axios from 'axios';
import * as Globals from '@/bootstrap/globals';
import Listing, { injectListingContext } from '@/components/ui/Listing/Listing.vue';

vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
        isCancel: vi.fn(() => false),
    },
}));

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));

window.Statamic = {
    $config: {
        get: (key, fallback) => {
            if (key === 'paginationSizeOptions') return [10, 25, 50, 100];
            if (key === 'paginationSize') return 50;
            return fallback;
        },
    },
    $progress: { loading: () => {}, complete: () => {} },
    $preferences: { get: () => undefined },
    $events: { $on: () => {}, $off: () => {}, $emit: () => {} },
    $toast: { error: vi.fn(), success: vi.fn() },
};

const Probe = defineComponent({
    setup() {
        return { listing: injectListingContext() };
    },
    render() {
        return h('div');
    },
});

function mountListing(props) {
    return mount(Listing, {
        props: {
            items: [],
            ...props,
        },
        slots: { default: () => h(Probe) },
    });
}

test('counts active filter badges that belong to the listing filters', () => {
    const wrapper = mountListing({ filters: [{ handle: 'author', auto_apply: [] }, { handle: 'status', auto_apply: [] }] });
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.activeFilterBadges.value = { author: 'John', status: 'Published' };

    expect(listing.activeFilterBadgeCount.value).toBe(2);
});

test('ignores active filter badges that are not configured filters', () => {
    const wrapper = mountListing({ filters: [{ handle: 'author', auto_apply: [] }] });
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.activeFilterBadges.value = { author: 'John', site: { site: 'default' } };

    expect(listing.activeFilterBadgeCount.value).toBe(1);
});

test('counts each nested field badge individually', () => {
    const wrapper = mountListing({ filters: [{ handle: 'fields', auto_apply: [] }] });
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.activeFilterBadges.value = { fields: { title: 'Foo', slug: 'bar' } };

    expect(listing.activeFilterBadgeCount.value).toBe(2);
});

test('exposes select-all-matching helpers on the listing context', () => {
    const wrapper = mountListing({
        items: [{ id: 'a' }, { id: 'b' }],
        url: '/cp/collections/test/entries',
    });
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.meta.value = { total: 25 };
    listing.selections.value = ['a', 'b'];

    // Client `items` prop wins over url for canSelectAllMatching.
    expect(listing.canSelectAllMatching.value).toBe(false);
    expect(listing.allMatchingSelected.value).toBe(false);
    expect(typeof listing.selectAllMatching).toBe('function');
});

test('canSelectAllMatching can be disabled via allowSelectAllMatching', async () => {
    axios.get.mockResolvedValue({
        data: {
            data: [{ id: '1' }, { id: '2' }],
            meta: { last_page: 3, total: 5, per_page: 2, columns: [] },
        },
    });

    const wrapper = mountListing({
        url: '/cp/assets/browse/folders/main',
        items: undefined,
        actionUrl: '/cp/assets/actions',
        allowSelectAllMatching: false,
        perPage: 2,
    });

    await flushPromises();

    const { listing } = wrapper.findComponent(Probe).vm;

    listing.meta.value = { total: 5, per_page: 2, last_page: 3, columns: [] };
    listing.items.value = [{ id: '1' }, { id: '2' }];
    listing.selections.value = ['1', '2'];

    expect(listing.canSelectAllMatching.value).toBe(false);
});

test('canSelectAllMatching is hidden when total exceeds selectAllLimit', async () => {
    const previousGet = window.Statamic.$config.get;
    window.Statamic.$config.get = (key, fallback) => {
        if (key === 'selectAllLimit') return 3;
        return previousGet(key, fallback);
    };

    axios.get.mockResolvedValue({
        data: {
            data: [{ id: '1' }, { id: '2' }],
            meta: { last_page: 3, total: 5, per_page: 2, columns: [] },
        },
    });

    const wrapper = mountListing({
        url: '/cp/collections/test/entries',
        items: undefined,
        actionUrl: '/cp/collections/test/entries/actions',
        perPage: 2,
    });

    await flushPromises();

    const { listing } = wrapper.findComponent(Probe).vm;

    listing.meta.value = { total: 5, per_page: 2, last_page: 3, columns: [] };
    listing.items.value = [{ id: '1' }, { id: '2' }];
    listing.selections.value = ['1', '2'];

    expect(listing.canSelectAllMatching.value).toBe(false);

    window.Statamic.$config.get = previousGet;
});

test('selectAllMatching pages through listing results past the perPage ceiling', async () => {
    axios.get.mockImplementation(async (url, { params }) => {
        const pages = {
            1: {
                data: [{ id: '1' }, { id: '2' }],
                meta: { last_page: 3, total: 5, per_page: 2, columns: [] },
            },
            2: {
                data: [{ id: '3' }, { id: '4' }],
                meta: { last_page: 3, total: 5, per_page: 2, columns: [] },
            },
            3: {
                data: [{ id: '5' }],
                meta: { last_page: 3, total: 5, per_page: 2, columns: [] },
            },
        };

        return { data: pages[params.page] };
    });

    const wrapper = mountListing({
        url: '/cp/collections/test/entries',
        items: undefined,
        actionUrl: '/cp/collections/test/entries/actions',
        perPage: 2,
    });

    await flushPromises();

    const { listing } = wrapper.findComponent(Probe).vm;

    listing.selections.value = ['1', '2'];

    expect(listing.canSelectAllMatching.value).toBe(true);

    await listing.selectAllMatching();
    await flushPromises();

    expect(listing.selections.value).toEqual(['1', '2', '3', '4', '5']);
    expect(listing.allMatchingSelected.value).toBe(true);
    expect(listing.canSelectAllMatching.value).toBe(false);
    expect(axios.get.mock.calls.some((call) => call[1]?.params?.page === 3)).toBe(true);
    expect(axios.get.mock.calls.some((call) => call[1]?.params?.perPage === 100)).toBe(true);
    expect(axios.get.mock.calls.some((call) => call[1]?.params?.columns === 'id')).toBe(true);
});

test('selectAllMatching stays selected when fetched ids are fewer than meta.total', async () => {
    axios.get.mockImplementation(async (url, { params }) => {
        const pages = {
            1: {
                data: [{ id: '1' }, { id: '2' }],
                meta: { last_page: 2, total: 5, per_page: 2, columns: [] },
            },
            2: {
                data: [{ id: '3' }, { id: '4' }],
                meta: { last_page: 2, total: 5, per_page: 2, columns: [] },
            },
        };

        return { data: pages[params.page] };
    });

    const wrapper = mountListing({
        url: '/cp/collections/test/entries',
        items: undefined,
        actionUrl: '/cp/collections/test/entries/actions',
        perPage: 2,
    });

    await flushPromises();

    const { listing } = wrapper.findComponent(Probe).vm;

    listing.meta.value = { total: 5, per_page: 2, last_page: 3, columns: [] };
    listing.items.value = [{ id: '1' }, { id: '2' }];
    listing.selections.value = ['1', '2'];

    await listing.selectAllMatching();
    await flushPromises();

    expect(listing.selections.value).toEqual(['1', '2', '3', '4']);
    expect(listing.selectedAllMatching.value).toBe(true);
    expect(listing.allMatchingSelected.value).toBe(true);
    expect(listing.canSelectAllMatching.value).toBe(false);
});

test('selectedAllMatching clears when a selection is removed after select-all', async () => {
    const wrapper = mountListing({
        items: [{ id: 'a' }, { id: 'b' }],
        url: '/cp/collections/test/entries',
    });
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.meta.value = { total: 4 };
    listing.selectedAllMatchingCount.value = 4;
    listing.selections.value = ['a', 'b', 'c', 'd'];
    listing.selectedAllMatching.value = true;

    listing.selections.value.splice(3, 1);
    await flushPromises();

    expect(listing.selectedAllMatching.value).toBe(false);
});

test('allMatchingSelected clears when listing filters change', async () => {
    const wrapper = mountListing({
        items: [{ id: 'a' }, { id: 'b' }],
        url: '/cp/collections/test/entries',
        filters: [{ handle: 'status', auto_apply: [] }],
    });
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.meta.value = { total: 2 };
    listing.selections.value = ['a', 'b'];
    listing.selectedAllMatching.value = true;

    expect(listing.allMatchingSelected.value).toBe(true);

    listing.setFilter('status', { status: 'published' });
    await flushPromises();

    expect(listing.allMatchingSelected.value).toBe(false);
});

test('allMatchingSelected clears when listing url changes', async () => {
    const wrapper = mountListing({
        items: [{ id: 'a' }, { id: 'b' }],
        url: '/cp/assets/browse/folders/main',
    });
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.meta.value = { total: 2 };
    listing.selections.value = ['a', 'b'];
    listing.selectedAllMatching.value = true;

    await wrapper.setProps({ url: '/cp/assets/browse/folders/main/subfolder' });
    await flushPromises();

    expect(listing.selectedAllMatching.value).toBe(false);
});

test('allMatchingSelected clears when additionalParameters change', async () => {
    const wrapper = mountListing({
        items: [{ id: 'a' }, { id: 'b' }],
        url: '/cp/users',
        additionalParameters: { group: 'editors' },
    });
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.meta.value = { total: 2 };
    listing.selections.value = ['a', 'b'];
    listing.selectedAllMatching.value = true;

    await wrapper.setProps({ additionalParameters: { group: 'admins' } });
    await flushPromises();

    expect(listing.selectedAllMatching.value).toBe(false);
});

test('canSelectAllMatching stays hidden after perPage changes when all ids are selected', async () => {
    axios.get.mockResolvedValue({
        data: {
            data: [{ id: '1' }, { id: '2' }],
            meta: { last_page: 3, total: 5, per_page: 2, columns: [] },
        },
    });

    const wrapper = mountListing({
        url: '/cp/collections/test/entries',
        items: undefined,
        actionUrl: '/cp/collections/test/entries/actions',
        perPage: 2,
    });

    await flushPromises();

    const { listing } = wrapper.findComponent(Probe).vm;

    listing.meta.value = { total: 5, per_page: 2, last_page: 3, columns: [] };
    listing.items.value = [{ id: '1' }, { id: '2' }];
    listing.selections.value = ['1', '2', '3', '4', '5'];
    listing.selectedAllMatching.value = true;

    expect(listing.canSelectAllMatching.value).toBe(false);

    listing.setPerPage(10);
    await flushPromises();

    expect(listing.selectedAllMatching.value).toBe(true);
    expect(listing.canSelectAllMatching.value).toBe(false);
});
