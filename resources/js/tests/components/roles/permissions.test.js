import { describe, expect, test } from 'vitest';
import { allVisibleChecked, checkVisible, isVisible, uncheckAll, visible } from '@/components/roles/permissions.js';

const permission = (value, overrides = {}) => ({
    value,
    checked: false,
    hidden_by: [],
    children: [],
    ...overrides,
});

const values = (permissions) => permissions.map((permission) => permission.value);

const flatten = (permissions) => permissions.flatMap((p) => [p, ...flatten(p.children)]);

const checkedValues = (permissions) =>
    flatten(permissions)
        .filter((permission) => permission.checked)
        .map((permission) => permission.value);

describe('isVisible', () => {
    test('a permission with no hiders is always visible', () => {
        expect(isVisible(permission('one'), [])).toBe(true);
        expect(isVisible(permission('one'), ['two'])).toBe(true);
    });

    test('a permission is hidden when any of its hiders are checked', () => {
        const one = permission('one', { hidden_by: ['two', 'three'] });

        expect(isVisible(one, [])).toBe(true);
        expect(isVisible(one, ['four'])).toBe(true);
        expect(isVisible(one, ['two'])).toBe(false);
        expect(isVisible(one, ['three'])).toBe(false);
        expect(isVisible(one, ['two', 'three'])).toBe(false);
    });
});

describe('visible', () => {
    test('it filters out hidden permissions', () => {
        const permissions = [
            permission('configure collections'),
            permission('view blog entries', { hidden_by: ['configure collections'] }),
            permission('view users'),
        ];

        expect(values(visible(permissions, []))).toEqual([
            'configure collections',
            'view blog entries',
            'view users',
        ]);

        expect(values(visible(permissions, ['configure collections']))).toEqual([
            'configure collections',
            'view users',
        ]);
    });
});

describe('allVisibleChecked', () => {
    test('it is true when every visible permission is checked', () => {
        const permissions = [
            permission('configure collections', { checked: true }),
            permission('view blog entries', { hidden_by: ['configure collections'] }),
        ];

        expect(allVisibleChecked(permissions, ['configure collections'])).toBe(true);
        expect(allVisibleChecked(permissions, [])).toBe(false);
    });

    test('it recurses into visible children only', () => {
        const permissions = [
            permission('configure collections', { checked: true }),
            permission('view blog entries', {
                checked: true,
                children: [
                    permission('publish blog entries', { hidden_by: ['configure collections'] }),
                    permission('delete blog entries', { checked: true }),
                ],
            }),
        ];

        expect(allVisibleChecked(permissions, ['configure collections'])).toBe(true);
        expect(allVisibleChecked(permissions, [])).toBe(false);
    });

    test('an unchecked visible permission makes it false', () => {
        const permissions = [permission('one', { checked: true }), permission('two')];

        expect(allVisibleChecked(permissions, [])).toBe(false);
    });

    test('an empty set is vacuously true, so callers must guard the top level', () => {
        expect(allVisibleChecked([], [])).toBe(true);
    });
});

describe('checkVisible', () => {
    test('it checks visible permissions and their visible children', () => {
        const permissions = [
            permission('configure collections'),
            permission('view blog entries', {
                hidden_by: ['configure collections'],
                children: [permission('delete blog entries')],
            }),
        ];

        checkVisible(permissions, ['configure collections']);

        expect(checkedValues(permissions)).toEqual(['configure collections']);
    });

    test('it does not let the permissions it checks hide the ones that follow', () => {
        const permissions = [
            permission('configure collections'),
            permission('view blog entries', { hidden_by: ['configure collections'] }),
        ];

        checkVisible(permissions, []);

        expect(checkedValues(permissions)).toEqual(['configure collections', 'view blog entries']);
    });
});

describe('uncheckAll', () => {
    test('it unchecks everything, including hidden permissions', () => {
        const permissions = [
            permission('configure collections', { checked: true }),
            permission('view blog entries', {
                checked: true,
                hidden_by: ['configure collections'],
                children: [permission('delete blog entries', { checked: true })],
            }),
        ];

        uncheckAll(permissions);

        expect(checkedValues(permissions)).toEqual([]);
    });
});
