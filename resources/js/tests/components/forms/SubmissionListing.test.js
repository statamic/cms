import { mount } from '@vue/test-utils';
import { expect, test, vi } from 'vitest';
import SubmissionListing from '@/components/forms/SubmissionListing.vue';

vi.mock('@/components/ui', async () => {
    const { defineComponent, h } = await import('vue');

    const stub = (name, props = []) => defineComponent({
        name,
        props,
        setup: () => () => h('div', { 'data-component': name }),
    });

    return {
        Listing: stub('Listing', ['allowPresets', 'pushQuery']),
        DropdownItem: stub('DropdownItem'),
        Panel: stub('Panel'),
        PanelFooter: stub('PanelFooter'),
        ToggleGroup: stub('ToggleGroup'),
        ToggleItem: stub('ToggleItem'),
    };
});

vi.mock('@inertiajs/vue3', () => ({ Link: {} }));
vi.mock('@/components/forms/SubmissionStatusIndicator.vue', () => ({ default: {} }));
vi.mock('@/components/forms/summary/Summary.vue', () => ({ default: {} }));
vi.mock('@ui/Listing/Table.vue', () => ({ default: {} }));
vi.mock('@ui/Listing/Search.vue', () => ({ default: {} }));
vi.mock('@ui/Listing/Filters.vue', () => ({ default: {} }));
vi.mock('@ui/Listing/CustomizeColumns.vue', () => ({ default: {} }));
vi.mock('@ui/Listing/Pagination.vue', () => ({ default: {} }));
vi.mock('@ui/Listing/Presets.vue', () => ({ default: {} }));
vi.mock('@api', () => ({ preferences: { get: (key, fallback) => fallback, set: vi.fn() } }));

const mountListing = (props = {}) => {
    globalThis.cp_url = (url) => `/cp/${url}`;

    return mount(SubmissionListing, {
        props: {
            form: 'contact',
            actionUrl: '/actions',
            sortColumn: 'date',
            sortDirection: 'desc',
            columns: [],
            filters: [],
            ...props,
        },
    });
};

test('allows presets on the main listing', () => {
    const listing = mountListing().findComponent({ name: 'Listing' });

    expect(listing.props('allowPresets')).toBe(true);
    expect(listing.props('pushQuery')).toBe(true);
});

test('disables presets when viewing in a stack', () => {
    const listing = mountListing({ viewInStack: true }).findComponent({ name: 'Listing' });

    expect(listing.props('allowPresets')).toBe(false);
    expect(listing.props('pushQuery')).toBe(false);
});
