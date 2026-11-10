import { afterEach, expect, test } from 'vitest';
import { page } from 'vitest/browser';
import { flushPromises, mount } from '@vue/test-utils';
import { h } from 'vue';
import { RelationshipCreateButton } from '@/bootstrap/cms/core.js';
import { Button, Badge, Dropdown, DropdownMenu, DropdownLabel, DropdownItem } from '@ui';
import '../../../css/app.css';

window.__ = key => key;

let wrapper;
let host;
afterEach(() => {
    wrapper?.unmount();
    host?.remove();
});

function render(creatables, menuLabel) {
    host = document.createElement('div');
    document.body.appendChild(host);
    wrapper = mount(RelationshipCreateButton, {
        attachTo: host,
        props: { creatables, menuLabel, text: 'Create Item' },
        global: {
            mocks: { __: window.__ },
            components: {
                'ui-button': Button,
                'ui-badge': Badge,
                'ui-dropdown': Dropdown,
                'ui-dropdown-menu': DropdownMenu,
                'ui-dropdown-label': DropdownLabel,
                'ui-dropdown-item': DropdownItem,
            },
            stubs: {
                InlineCreateForm: {
                    props: ['itemUrl'],
                    setup: props => () => h('output', props.itemUrl),
                },
            },
        },
    });
}

test('no creatables renders no creation control; one opens the form directly', async () => {
    render([]);
    expect(wrapper.find('button').exists()).toBe(false);

    await wrapper.setProps({ creatables: [{ url: '/posts/create', label: 'Posts' }] });
    await page.getByRole('button', { name: 'Create Item' }).click();
    expect(wrapper.find('output').text()).toBe('/posts/create');
    expect(document.querySelector('[role="menu"]')).toBeNull();
});

test.each([
    [
        'Choose Blueprint',
        [{ url: '/article', blueprint: 'Article', parent_title: 'Blog' }, { url: '/link', blueprint: 'Link' }],
        'Article Blog',
        '/article',
    ],
    [
        'Choose Resource',
        [
            { url: '/post', label: 'Posts', hint: '', parent_title: 'Hidden legacy hint' },
            { url: '/video', label: 'Videos', hint: 'Media', blueprint: 'Legacy blueprint', parent_title: 'Legacy parent' },
        ],
        'Videos Media',
        '/video',
    ],
])('creation menu supports %s and opens the selected target', async (heading, creatables, label, url) => {
    render(creatables, heading === 'Choose Blueprint' ? undefined : heading);
    await page.getByRole('button', { name: 'Create Item' }).click();
    await expect.element(page.getByText(heading, { exact: true })).toBeVisible();
    expect(document.querySelectorAll('[data-ui-badge]')).toHaveLength(1);
    expect(document.querySelector('[role="menu"]').textContent).not.toMatch(/Legacy|Hidden/);
    await page.getByRole('menuitem', { name: label }).click();
    await flushPromises();
    expect(wrapper.find('output').text()).toBe(url);
});
