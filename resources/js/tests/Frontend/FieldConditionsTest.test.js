import { test, expect } from 'vitest';
import '../../frontend/helpers.js';

let formData = {
    target: 'test',
    first_name: 'Bilbo',
    last_name: 'Baggins',
    hobby: '',
    bio: null,
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
