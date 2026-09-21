import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';

globalThis.__ = (key) => key;

import Item from '@/components/inputs/relationship/Item.vue';
import { publishContextKey } from '@/components/ui';

const stubs = {
    'ui-icon': { props: ['name'], template: '<i :data-icon="name"></i>' },
    'ui-status-indicator': true,
    'inline-edit-form': true,
};

function mountItem(item) {
    return mount(Item, {
        props: { item, readOnly: true },
        global: {
            stubs,
            directives: { tooltip: {} },
            provide: {
                [publishContextKey]: { parentContainer: null, name: { value: 'test' } },
            },
        },
    });
}

describe('relationship Item path', () => {
    test('renders the path as a breadcrumb before the title', () => {
        const wrapper = mountItem({ title: 'T-Shirts', path: ['Clothing', 'Shirts'] });
        const path = wrapper.get('span[title]');

        expect(path.attributes('title')).toBe('Clothing › Shirts');
        expect(path.text()).toContain('Clothing');
        expect(path.text()).toContain('Shirts');
        expect(wrapper.findAll('[data-icon="chevron-right"]')).toHaveLength(2);
        expect(wrapper.text()).toContain('T-Shirts');

        wrapper.unmount();
    });

    test('collapses the middle of a long path, keeping the segments nearest the term', () => {
        const wrapper = mountItem({ title: 'Band Tees', path: ['Clothing', 'Shirts', 'T-Shirts', 'Graphic Tees', 'Novelty'] });
        const path = wrapper.get('span[title]');

        expect(path.text()).toContain('Clothing');
        expect(path.text()).toContain('…');
        expect(path.text()).toContain('Graphic Tees');
        expect(path.text()).toContain('Novelty');
        expect(path.text()).not.toContain('Shirts');
        expect(path.attributes('title')).toBe('Clothing › Shirts › T-Shirts › Graphic Tees › Novelty');

        wrapper.unmount();
    });

    test('renders no breadcrumb when there is no path', () => {
        const wrapper = mountItem({ title: 'Marketing' });

        expect(wrapper.find('span[title]').exists()).toBe(false);
        expect(wrapper.findAll('[data-icon="chevron-right"]')).toHaveLength(0);

        wrapper.unmount();
    });

    test('renders no breadcrumb when the path is empty', () => {
        const wrapper = mountItem({ title: 'Clothing', path: [] });

        expect(wrapper.find('span[title]').exists()).toBe(false);

        wrapper.unmount();
    });
});

describe('relationship Item hint', () => {
    test('renders the hint outside the title cluster', () => {
        const wrapper = mountItem({ title: 'My Post', hint: 'Blog' });

        const hint = wrapper.get('.text-2xs');

        expect(hint.text()).toBe('Blog');
        expect(hint.text()).not.toContain('My Post');

        wrapper.unmount();
    });

    test('renders the hint alongside a path', () => {
        const wrapper = mountItem({ title: 'T-Shirts', path: ['Clothing'], hint: 'Product Categories' });

        expect(wrapper.get('.text-2xs').text()).toBe('Product Categories');
        expect(wrapper.get('span[title]').text()).toContain('Clothing');

        wrapper.unmount();
    });
});
