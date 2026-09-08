import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { h } from 'vue';
import * as Globals from '@/bootstrap/globals';
import Listing from '@/components/ui/Listing/Listing.vue';
import Search from '@/components/ui/Listing/Search.vue';
import TableHead from '@/components/ui/Listing/TableHead.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));

window.Statamic = {
    $config: { get: () => undefined },
    $progress: { loading: () => {}, complete: () => {} },
    $preferences: { get: () => undefined },
    $events: { $on: () => {}, $off: () => {}, $emit: () => {} },
};

const mountInListing = (child, listingProps = {}) =>
    mount(Listing, { props: { items: [], ...listingProps }, slots: { default: () => child } });

test('the search label defaults to a listing-agnostic string', () => {
    const wrapper = mountInListing(h(Search));

    expect(wrapper.find('label').text()).toBe('Search');
});

test('the search label can be set per listing', () => {
    // Labels are translated at the call site, e.g. :label="__('Search assets')",
    // so the component renders the string it is given without translating again.
    const wrapper = mountInListing(h(Search, { label: 'Search assets' }));

    expect(wrapper.find('label').text()).toBe('Search assets');
});

test('the search label is associated with the input', () => {
    const wrapper = mountInListing(h(Search));
    const id = wrapper.find('input').attributes('id');

    expect(id).toBeTruthy();
    expect(wrapper.find('label').attributes('for')).toBe(id);
});

test('two search fields in the same listing do not share an id', () => {
    const wrapper = mountInListing(h('div', [h(Search), h(Search)]));
    const [a, b] = wrapper.findAll('input').map((input) => input.attributes('id'));

    expect(a).toBeTruthy();
    expect(b).toBeTruthy();
    expect(a).not.toBe(b);
});

test('the actions header cell has an accessible name', () => {
    const wrapper = mountInListing(h(TableHead), {
        columns: [{ field: 'title', label: 'Title' }],
        actionUrl: '/cp/collections/pages/actions',
    });
    const actionsHeader = wrapper.find('th.actions-column');

    expect(actionsHeader.exists()).toBe(true);
    expect(actionsHeader.text()).toBe('Actions');
});
