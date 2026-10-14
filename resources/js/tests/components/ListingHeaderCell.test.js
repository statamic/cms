import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { h } from 'vue';
import * as Globals from '@/bootstrap/globals';
import Listing from '@/components/ui/Listing/Listing.vue';
import HeaderCell from '@/components/ui/Listing/HeaderCell.vue';

Object.keys(Globals).forEach((fn) => (window[fn] = Globals[fn]));

window.Statamic = {
    $config: { get: () => undefined },
    $progress: { loading: () => {}, complete: () => {} },
    $preferences: { get: () => undefined },
    $events: { $on: () => {}, $off: () => {}, $emit: () => {} },
};

function mountHeaderCell(column, listingProps = {}) {
    const wrapper = mount(Listing, {
        props: { items: [], ...listingProps },
        slots: { default: () => h(HeaderCell, { column }) },
    });

    return wrapper.find('th');
}

const sortable = { field: 'title', label: 'Title', sortable: true };

test('a sortable column that is not the current sort column is aria-sort="none"', () => {
    expect(mountHeaderCell(sortable).attributes('aria-sort')).toBe('none');
});

test('the current sort column exposes its direction', () => {
    expect(mountHeaderCell(sortable, { sortColumn: 'title', sortDirection: 'asc' }).attributes('aria-sort')).toBe(
        'ascending',
    );

    expect(mountHeaderCell(sortable, { sortColumn: 'title', sortDirection: 'desc' }).attributes('aria-sort')).toBe(
        'descending',
    );
});

test('a column that is not sortable has no aria-sort', () => {
    const th = mountHeaderCell({ field: 'status', label: 'Status', sortable: false });

    expect(th.attributes('aria-sort')).toBeUndefined();
});
