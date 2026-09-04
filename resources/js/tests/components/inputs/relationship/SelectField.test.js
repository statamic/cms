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

function mountSelectField({ items = [], config = {}, extra = {} } = {}) {
    return mount(SelectField, {
        props: {
            items,
            url: '/test/select-field',
            config,
            ...extra,
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

describe('SelectField searchKeys', () => {
    test('searches by title and email for the users fieldtype', () => {
        const wrapper = mountSelectField({ config: { type: 'users' } });

        expect(wrapper.vm.searchKeys).toEqual(['title', 'email']);

        wrapper.unmount();
    });

    test('searches by title and path for the terms fieldtype', () => {
        const wrapper = mountSelectField({ config: { type: 'terms' } });

        expect(wrapper.vm.searchKeys).toEqual(['title', 'path']);

        wrapper.unmount();
    });

    test('is null for other relationship fieldtypes', () => {
        const wrapper = mountSelectField({ config: { type: 'entries' } });

        expect(wrapper.vm.searchKeys).toBeNull();

        wrapper.unmount();
    });
});

describe('SelectField placeholder', () => {
    test('defaults to Choose... for non-taggable fields', () => {
        const wrapper = mountSelectField({ config: { type: 'entries' } });

        expect(wrapper.vm.fieldPlaceholder).toBe('Choose...');

        wrapper.unmount();
    });

    test('uses a create-friendly placeholder for taggable terms', () => {
        const wrapper = mountSelectField({
            config: { type: 'terms' },
            extra: { taggable: true },
        });

        expect(wrapper.vm.fieldPlaceholder).toBe('Search or create...');

        wrapper.unmount();
    });

    test('uses a create-friendly placeholder for hierarchical taggable terms', () => {
        const wrapper = mountSelectField({
            config: { type: 'terms' },
            extra: { taggable: true, tree: { url: '/taxonomies/categories/tree' } },
        });

        expect(wrapper.vm.fieldPlaceholder).toBe('Search or create...');

        wrapper.unmount();
    });

    test('prefers a custom placeholder over the create-friendly default', () => {
        const wrapper = mountSelectField({
            config: { type: 'terms', placeholder: 'Pick a category' },
            extra: { taggable: true, tree: { url: '/tree' } },
        });

        expect(wrapper.vm.fieldPlaceholder).toBe('Pick a category');

        wrapper.unmount();
    });
});

describe('SelectField typed term paths', () => {
    test('treats a delimiter-separated id as a typed path, not an existing term', () => {
        const wrapper = mountSelectField({ config: { type: 'terms' } });

        expect(wrapper.vm.isTypedTermPath('animals>cat')).toBe(true);
        expect(wrapper.vm.isTypedTermPath('categories::cat')).toBe(false);
        expect(wrapper.vm.termPathSegments('animals>cat>calico')).toEqual(['animals', 'cat', 'calico']);

        wrapper.unmount();
    });

    test('trims whitespace around segments in the spaced form', () => {
        const wrapper = mountSelectField({ config: { type: 'terms' } });

        expect(wrapper.vm.isTypedTermPath('Animals > Cat')).toBe(true);
        expect(wrapper.vm.termPathSegments('Animals > Cat > Calico')).toEqual(['Animals', 'Cat', 'Calico']);
        expect(wrapper.vm.newItemFromId('Animals > Cat > Calico')).toEqual({
            id: 'Animals > Cat > Calico',
            title: 'Calico',
            hint: 'Animals » Cat',
        });

        wrapper.unmount();
    });
});
