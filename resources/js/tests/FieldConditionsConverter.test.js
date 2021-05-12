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

test('it converts from blueprint format and applies prefixes', () => {
    let converted = FieldConditionsConverter.fromBlueprint({
        name: 'isnt joe',
        age: 13,
        email: 'equals san@holo.com',
    }, 'nested_');

    let expected = [
        {field: 'nested_name', operator: 'not', value: 'joe'},
        {field: 'nested_age', operator: 'equals', value: '13'},
        {field: 'nested_email', operator: 'equals', value: 'san@holo.com'}
    ];

    expect(converted).toEqual(expected);
});

test('it converts from blueprint format and does not apply prefix to root field conditions', () => {
    let converted = FieldConditionsConverter.fromBlueprint({
        'name': 'isnt joe',
        'root.title': 'not empty',
    }, 'nested_');

    let expected = [
        {field: 'nested_name', operator: 'not', value: 'joe'},
        {field: 'root.title', operator: 'not', value: 'empty'}
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
