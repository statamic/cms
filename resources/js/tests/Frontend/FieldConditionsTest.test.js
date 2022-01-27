import '../../frontend/helpers.js';

let formData = {
    first_name: 'Bilbo',
    last_name: 'Baggins',
    hobby: '',
    bio: null,
};

test('it shows field by default', () => {
    expect(Statamic.$conditions.showField([], formData)).toBe(true);
    expect(Statamic.$conditions.showField({}, formData)).toBe(true);
});

test('it can show field based on empty checks', () => {
    expect(Statamic.$conditions.showField({if: {hobby: 'empty'}}, formData)).toBe(true);
    expect(Statamic.$conditions.showField({if: {bio: 'empty'}}, formData)).toBe(true);
    expect(Statamic.$conditions.showField({if: {first_name: 'empty'}}, formData)).toBe(false);
    expect(Statamic.$conditions.showField({if: {first_name: 'not empty'}}, formData)).toBe(true);
});

test('it can show field if multiple conditions are met', () => {
    expect(Statamic.$conditions.showField({if: {first_name: 'Bilbo', last_name: 'Baggins'}}, formData)).toBe(true);
    expect(Statamic.$conditions.showField({if: {first_name: 'Frodo', last_name: 'Baggins'}}, formData)).toBe(false);
    expect(Statamic.$conditions.showField({if_any: {first_name: 'Frodo', last_name: 'Baggins'}}, formData)).toBe(true);
});
