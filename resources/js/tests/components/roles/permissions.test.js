import { describe, expect, test } from 'vitest';
import { isVisible, visible } from '@/components/roles/permissions.js';

const permission = (value, overrides = {}) => ({
    value,
    checked: false,
    hidden_by: [],
    children: [],
    ...overrides,
});

const values = (permissions) => permissions.map((permission) => permission.value);

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
