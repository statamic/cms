import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import PermissionTree from '@/components/roles/PermissionTree.vue';

const permission = (value, overrides = {}) => ({
    value,
    label: value,
    description: null,
    checked: false,
    hidden_by: [],
    children: [],
    ...overrides,
});

const mountTree = (permissions, checkedPermissions = []) =>
    mount(PermissionTree, {
        props: { depth: 1, initialPermissions: permissions, checkedPermissions },
        global: {
            stubs: {
                'ui-checkbox': {
                    props: ['value'],
                    template: '<div class="permission">{{ value }}</div>',
                },
            },
        },
    });

const renderedPermissions = (wrapper) => wrapper.findAll('.permission').map((el) => el.text());

test('it shows a permission when the permission hiding it is unchecked', () => {
    const wrapper = mountTree([
        permission('configure asset containers'),
        permission('view images assets', { hidden_by: ['configure asset containers'] }),
    ]);

    expect(renderedPermissions(wrapper)).toEqual(['configure asset containers', 'view images assets']);
});

test('it hides a permission when the permission hiding it is checked', () => {
    const wrapper = mountTree(
        [
            permission('configure asset containers', { checked: true }),
            permission('view images assets', { hidden_by: ['configure asset containers'] }),
        ],
        ['configure asset containers'],
    );

    expect(renderedPermissions(wrapper)).toEqual(['configure asset containers']);
});

test('it hides the children of a hidden permission', () => {
    const wrapper = mountTree(
        [
            permission('configure asset containers', { checked: true }),
            permission('view images assets', {
                hidden_by: ['configure asset containers'],
                children: [permission('delete images assets')],
            }),
        ],
        ['configure asset containers'],
    );

    expect(renderedPermissions(wrapper)).toEqual(['configure asset containers']);
});

test('it only hides permissions hidden by a checked permission', () => {
    const wrapper = mountTree(
        [
            permission('configure asset containers', { checked: true }),
            permission('view images assets', { hidden_by: ['configure asset containers'] }),
            permission('view blog entries', { hidden_by: ['configure collections'] }),
        ],
        ['configure asset containers'],
    );

    expect(renderedPermissions(wrapper)).toEqual(['configure asset containers', 'view blog entries']);
});

test('it hides a nested permission when the permission hiding it is checked', () => {
    const wrapper = mountTree(
        [
            permission('configure collections', { checked: true }),
            permission('view blog entries', {
                children: [permission('publish blog entries', { hidden_by: ['configure collections'] })],
            }),
        ],
        ['configure collections'],
    );

    expect(renderedPermissions(wrapper)).toEqual(['configure collections', 'view blog entries']);
});

test('it shows everything when no checked permissions are given', () => {
    const wrapper = mount(PermissionTree, {
        props: {
            depth: 1,
            initialPermissions: [permission('view images assets', { hidden_by: ['configure asset containers'] })],
        },
        global: {
            stubs: { 'ui-checkbox': { props: ['value'], template: '<div class="permission">{{ value }}</div>' } },
        },
    });

    expect(renderedPermissions(wrapper)).toEqual(['view images assets']);
});

test('it hides a permission when any of the permissions hiding it are checked', () => {
    const permissions = () => [
        permission('configure forms'),
        permission('edit forms'),
        permission('edit contact form', { hidden_by: ['configure forms', 'edit forms'] }),
    ];

    expect(renderedPermissions(mountTree(permissions(), ['configure forms']))).toEqual([
        'configure forms',
        'edit forms',
    ]);

    expect(renderedPermissions(mountTree(permissions(), ['edit forms']))).toEqual(['configure forms', 'edit forms']);

    expect(renderedPermissions(mountTree(permissions(), []))).toEqual([
        'configure forms',
        'edit forms',
        'edit contact form',
    ]);
});
