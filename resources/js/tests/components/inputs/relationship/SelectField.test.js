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
    test('passes the keys the fieldtype asked for through to the combobox', () => {
        const wrapper = mountSelectField({
            config: { type: 'terms' },
            extra: { searchKeys: ['title', 'search_titles'] },
        });

        expect(wrapper.findComponent({ name: 'Combobox' }).props('searchKeys')).toEqual([
            'title',
            'search_titles',
        ]);

        wrapper.unmount();
    });

    test('is null when the fieldtype did not ask for any', () => {
        const wrapper = mountSelectField({ config: { type: 'entries' } });

        expect(wrapper.findComponent({ name: 'Combobox' }).props('searchKeys')).toBeNull();

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

    test('prefers a custom placeholder over the create-friendly default', () => {
        const wrapper = mountSelectField({
            config: { type: 'terms', placeholder: 'Pick a category' },
            extra: { taggable: true },
        });

        expect(wrapper.vm.fieldPlaceholder).toBe('Pick a category');

        wrapper.unmount();
    });
});

describe('SelectField typed paths', () => {
    test('splits a typed path into a leaf title and its ancestors', () => {
        const wrapper = mountSelectField({ extra: { pathDelimiter: '>' } });

        expect(wrapper.vm.newItemFromId('animals>cat>calico')).toEqual({
            id: 'animals>cat>calico',
            title: 'calico',
            path: ['animals', 'cat'],
        });

        wrapper.unmount();
    });

    test('trims whitespace around segments in the spaced form', () => {
        const wrapper = mountSelectField({ extra: { pathDelimiter: '>' } });

        expect(wrapper.vm.newItemFromId('Animals > Cat > Calico')).toEqual({
            id: 'Animals > Cat > Calico',
            title: 'Calico',
            path: ['Animals', 'Cat'],
        });

        wrapper.unmount();
    });

    test('has no path when nothing was typed around the delimiter', () => {
        const wrapper = mountSelectField({ extra: { pathDelimiter: '>' } });

        expect(wrapper.vm.newItemFromId('Calico')).toEqual({ id: 'Calico', title: 'Calico' });
        expect(wrapper.vm.newItemFromId('>Calico')).toEqual({ id: '>Calico', title: '>Calico' });

        wrapper.unmount();
    });

    test('leaves the delimiter alone when the fieldtype did not ask for one', () => {
        const wrapper = mountSelectField();

        expect(wrapper.vm.newItemFromId('animals>cat')).toEqual({
            id: 'animals>cat',
            title: 'animals>cat',
        });

        wrapper.unmount();
    });
});
