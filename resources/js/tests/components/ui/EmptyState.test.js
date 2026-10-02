import { mount } from '@vue/test-utils';
import { afterEach, expect, test, vi } from 'vitest';
import { h } from 'vue';
import { Description, EmptyStateItem, EmptyStateMenu, Heading } from '@/components/ui';

vi.stubGlobal('Statamic', {
    $config: { get: () => '/cp' },
});

const uiComponents = {
    EmptyStateItem,
    'ui-heading': Heading,
    'ui-description': Description,
};

afterEach(() => {
    document.body.innerHTML = '';
});

test('menu heading is h2 and items are h3', () => {
    mount(EmptyStateMenu, {
        props: { heading: 'Getting Started' },
        slots: {
            default: () => [
                h(EmptyStateItem, {
                    href: '/cp/collections',
                    icon: 'collections',
                    heading: 'Create a Collection',
                }),
                h(EmptyStateItem, {
                    href: '/cp/blueprints',
                    icon: 'blueprints',
                    heading: 'Create a Blueprint',
                }),
            ],
        },
        global: { components: uiComponents },
        attachTo: document.body,
    });

    const headings = [...document.querySelectorAll('h1, h2, h3, h4')].map((el) => ({
        level: el.tagName,
        text: el.textContent.trim(),
    }));

    expect(headings).toEqual([
        { level: 'H2', text: 'Getting Started' },
        { level: 'H3', text: 'Create a Collection' },
        { level: 'H3', text: 'Create a Blueprint' },
    ]);
});

test('item heading level can be overridden', () => {
    mount(EmptyStateItem, {
        props: {
            href: '/cp/collections',
            icon: 'collections',
            heading: 'Create a Collection',
            level: 2,
        },
        global: { components: uiComponents },
        attachTo: document.body,
    });

    expect(document.querySelector('[data-ui-heading]')?.tagName).toBe('H2');
});
