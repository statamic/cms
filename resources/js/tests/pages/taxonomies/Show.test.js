import { mount, flushPromises } from '@vue/test-utils';
import { beforeEach, expect, test, vi } from 'vitest';
import Show from '@/pages/taxonomies/Show.vue';
import PageTree from '@/components/structures/PageTree.vue';

vi.mock('@inertiajs/vue3', () => ({
    Link: { name: 'Link', render: () => null },
    Head: { name: 'Head', render: () => null },
    router: { get: () => {}, on: () => () => {} },
    usePage: () => ({ props: {} }),
}));

let dirty;

async function mountPage() {
    const wrapper = mount(Show, {
        props: {
            taxonomy: 'tags',
            taxonomyTitle: 'Tags',
            blueprints: [{ handle: 'tag', title: 'Tag' }],
            initialSite: 'en',
            sites: ['en'],
            columns: [],
            filters: [],
            canCreate: true,
            canReorder: true,
            canEdit: true,
            canDelete: true,
            canConfigureFields: true,
            createUrl: '/cp/taxonomies/tags/terms/create',
            structured: true,
            structurePagesUrl: '/cp/taxonomies/tags/tree',
            structureSubmitUrl: '/cp/taxonomies/tags/tree',
            structureMaxDepth: null,
        },
        global: {
            stubs: {
                Head: true,
                Header: true,
                Dropdown: true,
                DropdownMenu: true,
                DropdownItem: true,
                DropdownLabel: true,
                DropdownSeparator: true,
                Listing: true,
                Button: true,
                ToggleGroup: true,
                ToggleItem: true,
                SiteSelector: true,
                ResourceDeleter: true,
                DeleteTermConfirmation: true,
                // The page registers PageTree asynchronously; swap in the real one so the
                // `canceled` wiring is genuinely exercised.
                PageTree,
                Draggable: true,
                TreeBranch: true,
                Panel: true,
                PanelHeader: true,
                Icon: true,
                ConfirmationModal: true,
            },
            mocks: {
                cp_url: (url) => `/cp/${url}`,
                __n: (key) => key,
                $dirty: dirty,
                $preferences: { get: () => 'tree', set: () => {} },
                $keys: { bindGlobal: () => ({ destroy: () => {} }) },
                $config: { get: (key, fallback) => fallback },
                $axios: { get: () => Promise.resolve({ data: { pages: [] } }) },
            },
        },
    });

    // The tree only renders once `mounted` picks the view.
    await flushPromises();

    return wrapper;
}

beforeEach(() => {
    const handles = new Set();

    dirty = {
        add: (handle) => handles.add(handle),
        remove: (handle) => handles.delete(handle),
        has: (handle) => handles.has(handle),
    };

    global.Statamic = {
        $commandPalette: { add: () => {}, category: { Actions: 'actions' } },
    };

    global.cp_url = (url) => `/cp/${url}`;
    global.__ = (key) => key;
});

test('discarding tree changes keeps staged deletions until the discard is confirmed', async () => {
    const wrapper = await mountPage();

    const tree = wrapper.vm.$refs.tree;

    wrapper.vm.markTreeDirty();
    wrapper.vm.deleteTreeBranch({ id: 'tags::foo', children: [] }, () => {});
    wrapper.vm.termDeletionConfirmCallback(false);
    expect(wrapper.vm.deletedTerms).toEqual(['tags::foo']);

    // Opening the confirmation modal must not discard anything yet.
    wrapper.vm.cancelTreeProgress();
    expect(tree.discardingChanges).toBe(true);
    expect(wrapper.vm.deletedTerms).toEqual(['tags::foo']);
    expect(wrapper.vm.treeIsDirty).toBe(true);

    // Dismissing the modal leaves the staged deletion intact.
    tree.discardingChanges = false;
    expect(wrapper.vm.deletedTerms).toEqual(['tags::foo']);
    expect(wrapper.vm.treeIsDirty).toBe(true);
});

test('confirming the discard clears staged deletions and the dirty state', async () => {
    const wrapper = await mountPage();

    const tree = wrapper.vm.$refs.tree;

    wrapper.vm.markTreeDirty();
    wrapper.vm.deleteTreeBranch({ id: 'tags::foo', children: [] }, () => {});
    wrapper.vm.termDeletionConfirmCallback(false);

    wrapper.vm.cancelTreeProgress();
    tree.confirmDiscard();
    await flushPromises();

    expect(wrapper.vm.deletedTerms).toEqual([]);
    expect(wrapper.vm.treeIsDirty).toBe(false);
});
