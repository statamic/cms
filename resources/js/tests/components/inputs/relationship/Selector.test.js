import { flushPromises, shallowMount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { h } from 'vue';
import Selector from '@/components/inputs/relationship/Selector.vue';

test('composes toolbar controls alongside the ordinary search and filters', async () => {
    const wrapper = shallowMount(Selector, {
        props: {
            name: 'composed',
            initialSelections: [],
            filtersUrl: '/filters',
        },
        slots: {
            toolbar: () => h('button', 'Choose resource'),
        },
        global: {
            mocks: {
                __n: key => key,
                $axios: { get: () => Promise.resolve({ data: [{ handle: 'status' }] }) },
            },
            stubs: {
                Listing: { template: '<div><slot /></div>' },
                'ui-badge': true,
            },
        },
    });
    await flushPromises();

    expect(wrapper.find('button').text()).toBe('Choose resource');
    expect(wrapper.find('search-stub').exists()).toBe(true);
    expect(wrapper.find('filters-stub').exists()).toBe(true);
    wrapper.unmount();
});
