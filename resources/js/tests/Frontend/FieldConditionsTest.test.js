import { test, expect } from 'vitest';
import '../../frontend/helpers.js';

let formData = {
    target: 'test',
    first_name: 'Bilbo',
    last_name: 'Baggins',
    hobby: '',
    bio: null,
    group_field: {
        dwelling: 'Bag End',
        village: 'Hobbiton',
        nested_group_field: {
            birthday: 'Sept 22',
            age: '111',
        },
    },
};

let showField = function (conditions, dottedFieldPath = null) {
    return Statamic.$conditions.showField(conditions, formData, dottedFieldPath || 'target');
};

test('it shows field by default', () => {
    expect(showField([])).toBe(true);
    expect(showField({})).toBe(true);
});

test('it can show field based on empty checks', () => {
    expect(showField({ if: { hobby: 'empty' } })).toBe(true);
    expect(showField({ if: { bio: 'empty' } })).toBe(true);
    expect(showField({ if: { first_name: 'empty' } })).toBe(false);
    expect(showField({ if: { first_name: 'not empty' } })).toBe(true);
});

test('it can show field if multiple conditions are met', () => {
    expect(showField({ if: { first_name: 'Bilbo', last_name: 'Baggins' } })).toBe(true);
    expect(showField({ if: { first_name: 'Frodo', last_name: 'Baggins' } })).toBe(false);
    expect(showField({ if_any: { first_name: 'Frodo', last_name: 'Baggins' } })).toBe(true);
});

test('it can show field nested in group based on sibling field value', () => {
    expect(showField({ if: { village: 'Hobbiton' } }, 'group_field.dwelling')).toBe(true);
    expect(showField({ if: { village: 'Mordor' } }, 'group_field.dwelling')).toBe(false);
});

test('it can show deeply field nested in group based on sibling field value', () => {
    expect(showField({ if: { age: 111 } }, 'group_field.nested_group_field.birthday')).toBe(true);
    expect(showField({ if: { age: 112 } }, 'group_field.nested_group_field.birthday')).toBe(false);
});

test('it can show deeply field nested in group based on parent field value', () => {
    expect(showField({ if: { '$parent.village': 'Hobbiton' } }, 'group_field.nested_group_field.birthday')).toBe(true);
    expect(showField({ if: { '$parent.village': 'Mordor' } }, 'group_field.nested_group_field.birthday')).toBe(false);
    expect(showField({ if: { '$parent.$parent.first_name': 'not empty' } }, 'group_field.nested_group_field.birthday')).toBe(true);
    expect(showField({ if: { '$parent.$parent.hobby': 'not empty' } }, 'group_field.nested_group_field.birthday')).toBe(false);
});

test('it can show deeply field nested in group based on root field value', () => {
    expect(showField({ if: { '$root.first_name': 'not empty' } }, 'group_field.nested_group_field.birthday')).toBe(true);
    expect(showField({ if: { '$root.hobby': 'not empty' } }, 'group_field.nested_group_field.birthday')).toBe(false);
});
