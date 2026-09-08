import { flushPromises, mount } from '@vue/test-utils';
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

// Renders the option slot the real combobox would, so the option row can be asserted on.
const optionRenderingStubs = {
    Combobox: {
        name: 'Combobox',
        props: ['options'],
        template: '<div><div v-for="option in options" class="option"><slot name="option" v-bind="option" /></div></div>',
    },
    StatusIndicator: true,
    'ui-icon': { props: ['name'], template: '<i :data-icon="name"></i>' },
    'ui-badge': { props: ['text'], template: '<span class="badge">{{ text }}</span>' },
};

function mountSelectField({ items = [], config = {}, extra = {}, stubs: overrides = stubs, options = [], get = null } = {}) {
    return mount(SelectField, {
        props: {
            items,
            url: '/test/select-field',
            config,
            ...extra,
        },
        global: {
            mocks: {
                $axios: { get: get ?? (() => Promise.resolve({ data: { data: options } })) },
            },
            stubs: overrides,
        },
    });
}

async function mountOptions(options) {
    const wrapper = mountSelectField({ stubs: optionRenderingStubs, options });
    await flushPromises();

    return wrapper;
}

async function type(wrapper, query) {
    wrapper.findComponent({ name: 'Combobox' }).vm.$emit('search', query, () => {});
    await flushPromises();
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

describe('SelectField option hierarchy', () => {
    test('indents an option that came from a tree ordered list', async () => {
        const wrapper = await mountOptions([{ id: '1', title: 'Cat', depth: 2 }]);

        const option = wrapper.get('.option > div');

        expect(option.attributes('style')).toContain('padding-inline-start: 0.75rem');
        expect(option.find('[data-icon="arrow-down-right"]').exists()).toBe(true);
        expect(option.findAll('.badge')).toHaveLength(0);

        wrapper.unmount();
    });

    test('renders a breadcrumb on an option that did not', async () => {
        const wrapper = await mountOptions([{ id: '1', title: 'Cat', path: ['Animals'] }]);

        const option = wrapper.get('.option > div');

        expect(option.attributes('style')).toBeUndefined();
        expect(option.find('[data-icon="arrow-down-right"]').exists()).toBe(false);
        expect(option.findAll('.badge').map((badge) => badge.text())).toEqual(['Animals']);
        expect(option.findAll('[data-icon="chevron-right"]')).toHaveLength(1);

        wrapper.unmount();
    });

    test('renders neither for an option with no hierarchy at all', async () => {
        const wrapper = await mountOptions([{ id: '1', title: 'Featured' }]);

        const option = wrapper.get('.option > div');

        expect(option.find('[data-icon="arrow-down-right"]').exists()).toBe(false);
        expect(option.findAll('.badge')).toHaveLength(0);
        expect(option.text()).toContain('Featured');

        wrapper.unmount();
    });
});

describe('SelectField option hierarchy while filtering', () => {
    test('swaps the indent for a breadcrumb while a query filters the list', async () => {
        const wrapper = await mountOptions([{ id: '1', title: 'Cat', depth: 2, path: ['Animals'] }]);

        await type(wrapper, 'cat');

        const option = wrapper.get('.option > div');

        expect(option.attributes('style')).toBeUndefined();
        expect(option.find('[data-icon="arrow-down-right"]').exists()).toBe(false);
        expect(option.findAll('.badge').map((badge) => badge.text())).toEqual(['Animals']);

        wrapper.unmount();
    });

    test('puts the indent back when the query is cleared', async () => {
        const wrapper = await mountOptions([{ id: '1', title: 'Cat', depth: 2, path: ['Animals'] }]);

        await type(wrapper, 'cat');
        await type(wrapper, '');

        const option = wrapper.get('.option > div');

        expect(option.attributes('style')).toContain('padding-inline-start: 0.75rem');
        expect(option.find('[data-icon="arrow-down-right"]').exists()).toBe(true);
        expect(option.findAll('.badge')).toHaveLength(0);

        wrapper.unmount();
    });

    test('does not request anything when typing in select mode', async () => {
        const get = vi.fn(() => Promise.resolve({ data: { data: [] } }));
        const wrapper = mountSelectField({ get });
        await flushPromises();

        expect(get).toHaveBeenCalledTimes(1);

        await type(wrapper, 'cat');

        expect(get).toHaveBeenCalledTimes(1);

        wrapper.unmount();
    });

    test('still searches on the server in typeahead mode', async () => {
        const get = vi.fn(() => Promise.resolve({ data: { data: [] } }));
        const wrapper = mountSelectField({ extra: { typeahead: true }, get });
        await flushPromises();

        expect(get).not.toHaveBeenCalled();

        await type(wrapper, 'cat');

        expect(get).toHaveBeenCalledTimes(1);
        expect(get.mock.calls[0][1].params).toMatchObject({ search: 'cat' });

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
