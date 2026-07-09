import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { ref } from 'vue';
import PermissionTree from '@/components/roles/PermissionTree.vue';

const permission = (value, overrides = {}) => ({
    value,
    label: value,
    description: null,
    superseded_by: null,
    checked: false,
    children: [],
    ...overrides,
});

const mountTree = (permissions, checkedPermissionValues = []) =>
    mount(PermissionTree, {
        props: { depth: 1, initialPermissions: permissions },
        global: {
            provide: { checkedPermissionValues: ref(checkedPermissionValues) },
            stubs: {
                'ui-checkbox': {
                    props: ['value'],
                    template: '<div class="permission">{{ value }}</div>',
                },
            },
        },
    });

const renderedPermissions = (wrapper) => wrapper.findAll('.permission').map((el) => el.text());

test('it shows superseded permissions when the superseding permission is unchecked', () => {
    const wrapper = mountTree([
        permission('configure asset containers'),
        permission('view images assets', { superseded_by: 'configure asset containers' }),
    ]);

    expect(renderedPermissions(wrapper)).toEqual(['configure asset containers', 'view images assets']);
});

test('it hides superseded permissions when the superseding permission is checked', () => {
    const wrapper = mountTree(
        [
            permission('configure asset containers', { checked: true }),
            permission('view images assets', { superseded_by: 'configure asset containers' }),
        ],
        ['configure asset containers'],
    );

    expect(renderedPermissions(wrapper)).toEqual(['configure asset containers']);
});

test('it hides the children of a superseded permission', () => {
    const wrapper = mountTree(
        [
            permission('configure asset containers', { checked: true }),
            permission('view images assets', {
                superseded_by: 'configure asset containers',
                children: [permission('delete images assets')],
            }),
        ],
        ['configure asset containers'],
    );

    expect(renderedPermissions(wrapper)).toEqual(['configure asset containers']);
});

test('it only hides permissions superseded by a checked permission', () => {
    const wrapper = mountTree(
        [
            permission('configure asset containers', { checked: true }),
            permission('view images assets', { superseded_by: 'configure asset containers' }),
            permission('view blog entries', { superseded_by: 'configure collections' }),
        ],
        ['configure asset containers'],
    );

    expect(renderedPermissions(wrapper)).toEqual(['configure asset containers', 'view blog entries']);
});
