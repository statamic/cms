import { shallowMount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import PublishForm from '@/components/roles/PublishForm.vue';

const permission = (value, overrides = {}) => ({
    value,
    label: value,
    description: null,
    checked: false,
    hidden_by: [],
    children: [],
    ...overrides,
});

const group = (handle, permissions) => ({ handle, label: handle, permissions });

const mountForm = (groups) =>
    shallowMount(PublishForm, {
        props: {
            initialTitle: 'Test',
            initialHandle: 'test',
            initialPermissions: groups,
            initialSuper: false,
            canAssignSuper: false,
            action: '/roles',
            method: 'post',
            indexUrl: '/roles',
        },
        global: {
            mocks: { $keys: { bindGlobal: () => {} } },
        },
    });

// The collections group, as core registers it: a configure permission that hides
// the per-collection permission sitting next to it.
const collectionsGroup = (overrides = {}) =>
    group('collections', [
        permission('configure collections', { checked: overrides.configure ?? false }),
        permission('view blog entries', {
            checked: overrides.view ?? false,
            hidden_by: ['configure collections'],
            children: [permission('delete blog entries', { checked: overrides.delete ?? false })],
        }),
    ]);

describe('areAllChecked', () => {
    test('it ignores hidden permissions', () => {
        const wrapper = mountForm([collectionsGroup({ configure: true })]);

        // "view blog entries" is unchecked, but hidden, so the group reads as fully checked.
        expect(wrapper.vm.areAllChecked(wrapper.vm.permissions[0])).toBe(true);
    });

    test('it is false while a visible permission is unchecked', () => {
        const wrapper = mountForm([collectionsGroup()]);

        expect(wrapper.vm.areAllChecked(wrapper.vm.permissions[0])).toBe(false);
    });

    test('it is false when a group has no visible permissions', () => {
        const wrapper = mountForm([
            group('cp', [permission('configure collections', { checked: true })]),
            group('collections', [permission('view blog entries', { hidden_by: ['configure collections'] })]),
        ]);

        // Nothing in the group renders, so the button must not offer to uncheck anything.
        expect(wrapper.vm.areAllChecked(wrapper.vm.permissions[1])).toBe(false);
    });
});

describe('toggleAllInGroup', () => {
    test('checking only checks what is visible', () => {
        // The hider is checked, so "view thing" is hidden. The unchecked sibling is what
        // leaves the group with something for Check All to do.
        const wrapper = mountForm([
            group('test', [
                permission('configure things', { checked: true }),
                permission('view thing', {
                    hidden_by: ['configure things'],
                    children: [permission('delete thing')],
                }),
                permission('view other thing'),
            ]),
        ]);
        const test = wrapper.vm.permissions[0];

        expect(wrapper.vm.areAllChecked(test)).toBe(false);

        wrapper.vm.toggleAllInGroup(test);

        expect(wrapper.vm.checkedPermissions).toEqual(['configure things', 'view other thing']);
    });

    test('checking an empty group checks everything, since nothing is hidden yet', () => {
        const wrapper = mountForm([collectionsGroup()]);
        const collections = wrapper.vm.permissions[0];

        wrapper.vm.toggleAllInGroup(collections);

        expect(collections.permissions[0].checked).toBe(true);
        expect(collections.permissions[1].checked).toBe(true);
        expect(collections.permissions[1].children[0].checked).toBe(true);
    });

    test('unchecking clears the whole group, including hidden permissions', () => {
        const wrapper = mountForm([collectionsGroup({ configure: true, view: true, delete: true })]);
        const collections = wrapper.vm.permissions[0];

        expect(wrapper.vm.areAllChecked(collections)).toBe(true);

        wrapper.vm.toggleAllInGroup(collections);

        expect(collections.permissions[0].checked).toBe(false);
        expect(collections.permissions[1].checked).toBe(false);
        expect(collections.permissions[1].children[0].checked).toBe(false);
    });

    test('one click always empties a fully checked group', () => {
        // Check All, then Uncheck All. The group must end up empty, not half revealed.
        const wrapper = mountForm([collectionsGroup()]);
        const collections = wrapper.vm.permissions[0];

        wrapper.vm.toggleAllInGroup(collections);
        expect(wrapper.vm.areAllChecked(collections)).toBe(true);

        wrapper.vm.toggleAllInGroup(collections);

        expect(wrapper.vm.checkedPermissions).toEqual([]);
        expect(wrapper.vm.areAllChecked(collections)).toBe(false);
    });
});

describe('toggleAllInAllGroups', () => {
    test('checking only checks what is visible in every group', () => {
        const wrapper = mountForm([
            collectionsGroup({ configure: true }),
            group('users', [permission('view users')]),
        ]);

        wrapper.vm.toggleAllInAllGroups();

        expect(wrapper.vm.checkedPermissions).toEqual(['configure collections', 'view users']);
    });

    test('checking a permission does not hide the ones checked after it', () => {
        // Visibility is snapshotted before anything is checked. Reading it as we walk would
        // let "configure things" hide "view thing" before we reach it.
        const wrapper = mountForm([
            group('one', [permission('configure things')]),
            group('two', [permission('view thing', { hidden_by: ['configure things'] })]),
        ]);

        wrapper.vm.toggleAllInAllGroups();

        expect(wrapper.vm.checkedPermissions).toEqual(['configure things', 'view thing']);
    });

    test('unchecking clears every group, including hidden permissions', () => {
        const wrapper = mountForm([
            collectionsGroup({ configure: true, view: true, delete: true }),
            group('users', [permission('view users', { checked: true })]),
        ]);

        expect(wrapper.vm.areAllCheckedInAllGroups()).toBe(true);

        wrapper.vm.toggleAllInAllGroups();

        expect(wrapper.vm.checkedPermissions).toEqual([]);
    });
});
