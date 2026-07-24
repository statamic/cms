import { mount } from '@vue/test-utils';
import { describe, expect, test, vi } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({
    router: { on: () => () => {} },
}));

globalThis.__ = (key) => key;

import { data_get } from '@/bootstrap/globals.js';
globalThis.data_get = data_get;

import SelectField from '@/components/inputs/relationship/SelectField.vue';

const stubs = {
    Combobox: true,
    StatusIndicator: true,
};

function mountSelectField({ items = [] } = {}) {
    return mount(SelectField, {
        props: {
            items,
            url: '/test/select-field',
            config: {},
        },
        global: {
            mocks: {
                $axios: { get: () => Promise.resolve({ data: { data: [] } }) },
            },
            stubs,
        },
    });
}

describe('SelectField comboboxOptions', () => {
    test('includes a selected item missing from the fetched options list', () => {
        const wrapper = mountSelectField({ items: [{ id: 'tags::bob', title: 'Bob' }] });

        expect(wrapper.vm.comboboxOptions).toContainEqual({ id: 'tags::bob', title: 'Bob' });

        wrapper.unmount();
    });

    test('does not duplicate an item already present in the fetched options list', async () => {
        const wrapper = mountSelectField({ items: [{ id: '1', title: 'One' }] });
        await wrapper.setData({ options: [{ id: '1', title: 'One' }] });

        expect(wrapper.vm.comboboxOptions).toEqual([{ id: '1', title: 'One' }]);

        wrapper.unmount();
    });
});
