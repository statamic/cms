import { mount, flushPromises } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';

const reload = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: (options) => reload(options) },
}));

vi.mock('@he-tree/vue', () => ({
    dragContext: {},
    Draggable: { template: '<div />' },
    walkTreeData: () => {},
}));

import Edit from '@/pages/preferences/nav/Edit.vue';

globalThis.clone = (value) => JSON.parse(JSON.stringify(value));
globalThis.Statamic = { $commandPalette: { add: () => {}, category: { Actions: 'Actions' } } };

function section(display, items) {
    return {
        display,
        display_original: display,
        action: false,
        items: items.map((item) => ({ display: item, children: [], manipulations: {} })),
    };
}

function mountEdit(nav) {
    return mount(Edit, {
        props: { title: 'My Nav', nav, updateUrl: '/update', destroyUrl: '/destroy' },
        global: {
            stubs: {
                Head: true,
                Header: true,
                Panel: true,
                PanelHeader: true,
                Draggable: true,
                ItemEditor: true,
                SectionEditor: true,
                ConfirmationModal: true,
                CommandPaletteItem: true,
            },
            mocks: {
                $axios: { delete: () => Promise.resolve() },
                $keys: { bindGlobal: () => ({ destroy: () => {} }) },
                $config: { get: (key, fallback) => fallback },
                $commandPalette: { category: { Actions: 'Actions' } },
                $toast: { error: () => {} },
            },
        },
    });
}

beforeEach(() => reload.mockClear());

test('it rebuilds the tree from the default nav after resetting customizations', async () => {
    const wrapper = mountEdit([section('Content 123', ['Globals', 'Collections'])]);
    expect(wrapper.vm.treeData[0].text).toBe('Content 123');

    wrapper.vm.reset();
    await flushPromises();

    expect(reload).toHaveBeenCalledOnce();

    reload.mock.calls[0][0].onSuccess({ props: { nav: [section('Content', ['Collections', 'Globals'])] } });

    expect(wrapper.vm.treeData[0].text).toBe('Content');
    expect(wrapper.vm.treeData[0].children.map((child) => child.text)).toEqual(['Collections', 'Globals']);
    expect(wrapper.vm.initialNav[0].display).toBe('Content');
});
