import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { defineComponent, h } from 'vue';
import * as Globals from '@/bootstrap/globals';
import Listing, { injectListingContext } from '@/components/ui/Listing/Listing.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));

window.Statamic = {
    $config: { get: () => undefined },
    $progress: { loading: () => {}, complete: () => {} },
    $preferences: { get: () => undefined },
    $events: { $on: () => {}, $off: () => {}, $emit: () => {} },
};

const Probe = defineComponent({
    setup() {
        return { listing: injectListingContext() };
    },
    render() {
        return h('div');
    },
});

function mountListing(filters) {
    return mount(Listing, {
        props: {
            items: [],
            filters,
            allowPresets: false,
            allowBulkActions: false,
            allowCustomizingColumns: false,
        },
        slots: { default: () => h(Probe) },
    });
}

test('counts active filter badges that belong to the listing filters', () => {
    const wrapper = mountListing([{ handle: 'author', auto_apply: [] }, { handle: 'status', auto_apply: [] }]);
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.activeFilterBadges.value = { author: 'John', status: 'Published' };

    expect(listing.activeFilterBadgeCount.value).toBe(2);
});

test('ignores active filter badges that are not configured filters', () => {
    const wrapper = mountListing([{ handle: 'author', auto_apply: [] }]);
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.activeFilterBadges.value = { author: 'John', site: { site: 'default' } };

    expect(listing.activeFilterBadgeCount.value).toBe(1);
});

test('counts each nested field badge individually', () => {
    const wrapper = mountListing([{ handle: 'fields', auto_apply: [] }]);
    const { listing } = wrapper.findComponent(Probe).vm;

    listing.activeFilterBadges.value = { fields: { title: 'Foo', slug: 'bar' } };

    expect(listing.activeFilterBadgeCount.value).toBe(2);
});
