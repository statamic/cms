import Converter from '../components/field-conditions/Converter.js';
global._ = require('underscore');

const FieldConditionsConverter = new Converter;

test('it converts from blueprint format', () => {
    let converted = FieldConditionsConverter.fromBlueprint({
        name: 'isnt joe',
        age: 13,
        email: 'equals san@holo.com',
    });

    let expected = [
        {field: 'name', operator: 'not', value: 'joe'},
        {field: 'age', operator: 'equals', value: '13'},
        {field: 'email', operator: 'equals', value: 'san@holo.com'}
    ];

    expect(converted).toEqual(expected);
});

test('it converts to blueprint format', () => {
    let converted = FieldConditionsConverter.toBlueprint([
        {field: 'name', operator: 'isnt', value: 'joe'},
        {field: 'age', operator: '==', value: '13'}
    ]);

    let expected = {
        name: 'isnt joe',
        age: '== 13'
    };

    expect(converted).toEqual(expected);
});

test('it converts and trims properly with empty operators', () => {
    let converted = FieldConditionsConverter.toBlueprint([
        {field: 'name', operator: '', value: 'joe'},
        {field: 'age', operator: null, value: '13'}
    ]);

    let expected = {
        name: 'joe',
        age: '13'
    };

    expect(converted).toEqual(expected);
});
