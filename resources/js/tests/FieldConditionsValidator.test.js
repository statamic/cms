import { test, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { nextTick } from 'vue';
import { data_get } from '../bootstrap/globals';
import FieldConditions from '@statamic/components/FieldConditions';
import PublishContainer from '@statamic/components/ui/Publish/Container.vue';
import ShowField from '@statamic/components/field-conditions/ShowField.js';

// Even though there's no Store anymore, this variable is named Store so that all the
// assertions don't need to be changed. This is now a reference to the PublishContainer component.
let Store;

let fieldLevelValues;
let fieldLevelExtraValues;

// This object exists purely so that tests could be refactored without needing to touch assertions.
// All the Fields.showField(...) could be changed to just showField(...)
let Fields = {
    showField: (condition, dottedFieldPath) => {
        return showField(condition, dottedFieldPath);
    }
}

const Statamic = {
    $conditions: new FieldConditions(),
    $dirty: {
        add: () => {},
    },
};
window.Statamic = Statamic;
window.__ = (msg) => msg;

const setValues = function (values, nestedKey) {
    fieldLevelValues = values;

    let storeValues = {};
    if (nestedKey) {
        storeValues[nestedKey] = values;
    } else {
        storeValues = values;
    }

    setStoreValues(storeValues);
};

const setExtraValues = function (values) {
    fieldLevelExtraValues = values;
};

const setStoreValues = function (values) {
    Store.setValues(values);
};

const setHiddenField = function (payload) {
    Store.setHiddenField(payload);
};

const setHiddenFieldsState = async function (fieldConfigs, dottedPrefix) {
    fieldConfigs
        .filter((fieldConfig) => fieldConfig.type === 'revealer')
        .forEach((fieldConfig) => {
            Store.setRevealerField(dottedPrefix ? `${dottedPrefix}.${fieldConfig.handle}` : fieldConfig.handle);
        });
    fieldConfigs.forEach((fieldConfig) => {
        Fields.showField(fieldConfig, dottedPrefix ? `${dottedPrefix}.${fieldConfig.handle}` : null);
    });
    await nextTick();
};

let showFieldIf = function (conditions = null, dottedFieldPath = null) {
    if (dottedFieldPath === null && conditions && Object.keys(conditions).length === 1) {
        dottedFieldPath = Object.keys(conditions)[0].replace(new RegExp('^\\$?root.'), '');
    }

    return Fields.showField(conditions ? { if: conditions } : {}, dottedFieldPath);
};

let showField = function(config, dottedFieldPath = null) {
    return new ShowField(
        fieldLevelValues ?? Store.values,
        fieldLevelExtraValues ?? Store.extraValues,
        Store.values,
        Store.hiddenFields,
        Store.revealerFields,
        Store.setHiddenField
    ).showField(config, dottedFieldPath);
}

beforeEach(() => {
    // Put a dummy component in the slot of the container so it doesn't render all the default stuff.
    const TestComponent = {
        template: '<div></div>',
    };

    // The PublishContainer is what will be set up for the test, and the test component will be used for the slot content.
    const publishContainer = mount(PublishContainer, {
        slots: {
            default: TestComponent,
        },
    });

    Store = publishContainer.vm;
});

test('it shows field by default', () => {
    expect(showFieldIf()).toBe(true);
});

test('it shows or hides field based on shorthand equals conditions', () => {
    setValues({ first_name: 'Jesse' });

    expect(showFieldIf({ first_name: 'Jesse' })).toBe(true);
    expect(showFieldIf({ first_name: 'Jack' })).toBe(false);
});

test('it can use comparison operators in conditions', () => {
    setValues({
        last_name: 'Hasselhoff',
        age: 13,
        string_age: '3',
    });

    expect(showFieldIf({ age: '== 13' })).toBe(true);
    expect(showFieldIf({ age: '!= 5' })).toBe(true);
    expect(showFieldIf({ last_name: '=== Hasselhoff' })).toBe(true);
    expect(showFieldIf({ last_name: '!== Fischer' })).toBe(true);
    expect(showFieldIf({ age: '=== 13' })).toBe(false); // We don't cast their condition on strict equality comparisons

    expect(showFieldIf({ age: '> 5' })).toBe(true);
    expect(showFieldIf({ age: '> 13' })).toBe(false);
    expect(showFieldIf({ age: '> 20' })).toBe(false);
    expect(showFieldIf({ age: '>= 13' })).toBe(true);

    expect(showFieldIf({ age: '< 5' })).toBe(false);
    expect(showFieldIf({ age: '< 13' })).toBe(false);
    expect(showFieldIf({ age: '< 20' })).toBe(true);
    expect(showFieldIf({ age: '<= 13' })).toBe(true);
    expect(showFieldIf({ string_age: '<= 13' })).toBe(true); // We cast to number when doing greater/less than comparisons

    expect(showFieldIf({ age: 'is 13' })).toBe(true);
    expect(showFieldIf({ age: 'equals 13' })).toBe(true);
    expect(showFieldIf({ age: 'not 13' })).toBe(false);
    expect(showFieldIf({ age: 'isnt 13' })).toBe(false);
    expect(showFieldIf({ age: '¯\\_(ツ)_/¯ 13' })).toBe(false);
});

test('it can use includes or contains operators in conditions', () => {
    setValues({
        cancellation_reasons: ['found another service', 'other'],
        example_string: 'The quick brown fox jumps over the lazy dog',
        age: 13,
        empty_string: '',
        null_value: null,
    });

    expect(showFieldIf({ cancellation_reasons: 'includes other' })).toBe(true);
    expect(showFieldIf({ cancellation_reasons: 'contains other' })).toBe(true);
    expect(showFieldIf({ cancellation_reasons: 'includes slow service' })).toBe(false);
    expect(showFieldIf({ cancellation_reasons: 'contains slow service' })).toBe(false);

    expect(showFieldIf({ example_string: 'includes fox jumps' })).toBe(true);
    expect(showFieldIf({ example_string: 'contains fox jumps' })).toBe(true);
    expect(showFieldIf({ example_string: 'includes dog jumps' })).toBe(false);
    expect(showFieldIf({ example_string: 'contains dog jumps' })).toBe(false);

    expect(showFieldIf({ age: 'includes 13' })).toBe(true);
    expect(showFieldIf({ age: 'contains 13' })).toBe(true);
    expect(showFieldIf({ age: 'includes fox' })).toBe(false);
    expect(showFieldIf({ age: 'contains fox' })).toBe(false);

    expect(showFieldIf({ empty_string: 'contains fox' })).toBe(false);
    expect(showFieldIf({ null_value: 'contains fox' })).toBe(false);
});

test('it can use includes_any or contains_any operators in conditions', () => {
    setValues({
        cancellation_reasons: ['found another service', 'other'],
        example_string: 'The quick brown fox jumps over the lazy dog',
        age: 13,
        empty_string: '',
        null_value: null,
    });

    expect(showFieldIf({ cancellation_reasons: 'includes_any sick, other' })).toBe(true);
    expect(showFieldIf({ cancellation_reasons: 'contains_any sick, other' })).toBe(true);
    expect(showFieldIf({ cancellation_reasons: 'includes_any sick, found another' })).toBe(false);
    expect(showFieldIf({ cancellation_reasons: 'contains_any sick, found another' })).toBe(false);

    expect(showFieldIf({ example_string: 'includes_any parrot, lazy dog' })).toBe(true);
    expect(showFieldIf({ example_string: 'contains_any parrot, lazy dog' })).toBe(true);
    expect(showFieldIf({ example_string: 'includes_any parrot, hops' })).toBe(false);
    expect(showFieldIf({ example_string: 'contains_any parrot, hops' })).toBe(false);

    expect(showFieldIf({ age: 'includes_any fox, 13' })).toBe(true);
    expect(showFieldIf({ age: 'contains_any fox, 13' })).toBe(true);
    expect(showFieldIf({ age: 'includes_any fox, 14' })).toBe(false);
    expect(showFieldIf({ age: 'contains_any fox, 14' })).toBe(false);

    expect(showFieldIf({ empty_string: 'contains_any fox, 13' })).toBe(false);
    expect(showFieldIf({ null_value: 'contains_any fox, 13' })).toBe(false);
});

test('it handles null, true, and false in condition as literal', () => {
    setValues({
        last_name: 'HasselHoff',
        likes_food: true,
        likes_animals: false,
        favorite_animal: null,
        not_real_boolean: 'false',
    });

    expect(showFieldIf({ first_name: '=== null' })).toBe(true);
    expect(showFieldIf({ last_name: '!== null' })).toBe(true);
    expect(showFieldIf({ likes_food: '=== true' })).toBe(true);
    expect(showFieldIf({ likes_animals: '=== false' })).toBe(true);
    expect(showFieldIf({ favorite_animal: '=== null' })).toBe(true);
    expect(showFieldIf({ not_real_boolean: '=== false' })).toBe(false);
});

test('it can check if value is empty', () => {
    setValues({
        last_name: 'HasselHoff',
        user: { email: 'david@hasselhoff.com' },
        favorite_foods: ['lasagna'],
        empty_string: '',
        empty_array: [],
        empty_object: {},
    });

    expect(showFieldIf({ first_name: 'empty' })).toBe(true);
    expect(showFieldIf({ last_name: 'is empty' })).toBe(false);
    expect(showFieldIf({ last_name: 'isnt empty' })).toBe(true);
    expect(showFieldIf({ last_name: 'not empty' })).toBe(true);
    expect(showFieldIf({ user: 'empty' })).toBe(false);
    expect(showFieldIf({ favorite_foods: 'empty' })).toBe(false);
    expect(showFieldIf({ empty_string: 'empty' })).toBe(true);
    expect(showFieldIf({ empty_array: 'empty' })).toBe(true);
    expect(showFieldIf({ empty_object: 'empty' })).toBe(true);
});

test('it can use operators with multi-word values', () => {
    setValues({ ace_ventura_says: 'Allllllrighty then!' });

    expect(showFieldIf({ ace_ventura_says: 'Allllllrighty then!' })).toBe(true);
    expect(showFieldIf({ ace_ventura_says: '== Allllllrighty then!' })).toBe(true);
    expect(showFieldIf({ ace_ventura_says: 'is Allllllrighty then!' })).toBe(true);
    expect(showFieldIf({ ace_ventura_says: 'not I am your father' })).toBe(true);
});

test('it only shows when multiple conditions are met', () => {
    setValues({
        first_name: 'San',
        last_name: 'Holo',
        age: 22,
    });

    expect(showFieldIf({ first_name: 'is San', last_name: 'is Holo', age: '!= 20' })).toBe(true);
    expect(showFieldIf({ first_name: 'is San', last_name: 'is Holo', age: '> 40' })).toBe(false);
});

test('it shows or hides with parent key variants', () => {
    setValues({
        first_name: 'Rincess',
        last_name: 'Pleia',
    });

    expect(Fields.showField({ if: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(true);
    expect(Fields.showField({ if: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(false);

    expect(Fields.showField({ show_when: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(true);
    expect(Fields.showField({ show_when: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(false);

    expect(Fields.showField({ unless: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(false);
    expect(Fields.showField({ unless: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(true);

    expect(Fields.showField({ hide_when: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(false);
    expect(Fields.showField({ hide_when: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(true);
});

test('it shows or hides when any of the conditions are met', () => {
    setValues({
        first_name: 'Rincess',
        last_name: 'Pleia',
    });

    expect(Fields.showField({ if_any: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(true);
    expect(Fields.showField({ if_any: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(true);
    expect(Fields.showField({ if_any: { first_name: 'is San', last_name: 'is Holo' } })).toBe(false);

    expect(Fields.showField({ show_when_any: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(true);
    expect(Fields.showField({ show_when_any: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(true);
    expect(Fields.showField({ show_when_any: { first_name: 'is San', last_name: 'is Holo' } })).toBe(false);

    expect(Fields.showField({ unless_any: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(false);
    expect(Fields.showField({ unless_any: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(false);
    expect(Fields.showField({ unless_any: { first_name: 'is San', last_name: 'is Holo' } })).toBe(true);

    expect(Fields.showField({ hide_when_any: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(false);
    expect(Fields.showField({ hide_when_any: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(false);
    expect(Fields.showField({ hide_when_any: { first_name: 'is San', last_name: 'is Holo' } })).toBe(true);
});

test('it can run conditions on nested data', () => {
    setValues(
        {
            name: 'Han',
            address: {
                country: 'Canada',
            },
        },
        'user',
    );

    expect(showFieldIf({ name: 'Han' })).toBe(true);
    expect(showFieldIf({ name: 'Chewy' })).toBe(false);
    expect(showFieldIf({ 'address.country': 'Canada' })).toBe(true);
    expect(showFieldIf({ 'address.country': 'Australia' })).toBe(false);
    expect(showFieldIf({ '$root.user.address.country': 'Canada' })).toBe(true);
    expect(showFieldIf({ '$root.user.address.country': 'Australia' })).toBe(false);
    expect(showFieldIf({ '$parent.name': 'Han' }, 'user.address.country')).toBe(true);
    expect(showFieldIf({ '$parent.name': 'Chewy' }, 'user.address.country')).toBe(false);
});

test('it can run conditions on parent data using parent syntax', () => {
    setValues({
        name: 'Han',
        replicator: [{ text: 'Foo' }, { text: 'Bar' }],
        group: {
            name: 'Chewy',
            text: 'Foo',
            replicator: [
                { text: 'Foo' },
                { text: 'Bar' },
                {
                    name: 'Luke',
                    replicator: [{ text: 'Foo' }],
                    group: {
                        name: 'Yoda',
                        replicator: [{ text: 'Foo' }],
                    },
                },
            ],
        },
    });

    // Test parent works from replicator to top level
    expect(showFieldIf({ '$parent.name': 'Han' }, 'replicator.0.text')).toBe(true);
    expect(showFieldIf({ '$parent.name': 'Chewy' }, 'replicator.0.text')).toBe(false);
    expect(showFieldIf({ '$parent.name': 'Han' }, 'replicator.1.text')).toBe(true);
    expect(showFieldIf({ '$parent.name': 'Chewy' }, 'replicator.1.text')).toBe(false);

    // Test parent works from nested field group to top level
    expect(showFieldIf({ '$parent.name': 'Han' }, 'group.text')).toBe(true);
    expect(showFieldIf({ '$parent.name': 'Chewy' }, 'group.text')).toBe(false);

    // Test parent works in deeply nested situations through multiple replicators and field groups
    expect(showFieldIf({ '$parent.name': 'Han' }, 'group.replicator.0.text')).toBe(false);
    expect(showFieldIf({ '$parent.name': 'Chewy' }, 'group.replicator.0.text')).toBe(true);
    expect(showFieldIf({ '$parent.name': 'Han' }, 'group.replicator.1.text')).toBe(false);
    expect(showFieldIf({ '$parent.name': 'Chewy' }, 'group.replicator.1.text')).toBe(true);
    expect(showFieldIf({ '$parent.name': 'Luke' }, 'group.replicator.2.replicator.0.text')).toBe(true);
    expect(showFieldIf({ '$parent.name': 'Leia' }, 'group.replicator.2.replicator.0.text')).toBe(false);
    expect(showFieldIf({ '$parent.name': 'Yoda' }, 'group.replicator.2.group.replicator.0.text')).toBe(true);
    expect(showFieldIf({ '$parent.name': 'Leia' }, 'group.replicator.2.group.replicator.0.text')).toBe(false);
    expect(showFieldIf({ '$parent.name': 'Luke' }, 'group.replicator.2.replicator.0.text')).toBe(true);
    expect(showFieldIf({ '$parent.name': 'Leia' }, 'group.replicator.2.replicator.0.text')).toBe(false);

    // Test parent can be chained to check upwards through multiple levels of multiple replicators and field groups
    expect(showFieldIf({ '$parent.$parent.name': 'Luke' }, 'group.replicator.2.group.replicator.0.text')).toBe(true);
    expect(showFieldIf({ '$parent.$parent.name': 'Leia' }, 'group.replicator.2.group.replicator.0.text')).toBe(false);
    expect(showFieldIf({ '$parent.$parent.$parent.name': 'Chewy' }, 'group.replicator.2.group.replicator.0.text')).toBe(
        true,
    );
    expect(showFieldIf({ '$parent.$parent.$parent.name': 'Leia' }, 'group.replicator.2.group.replicator.0.text')).toBe(
        false,
    );
    expect(
        showFieldIf({ '$parent.$parent.$parent.$parent.name': 'Han' }, 'group.replicator.2.group.replicator.0.text'),
    ).toBe(true);
    expect(
        showFieldIf({ '$parent.$parent.$parent.$parent.name': 'Leia' }, 'group.replicator.2.group.replicator.0.text'),
    ).toBe(false);
    expect(showFieldIf({ '$parent.$parent.name': 'Chewy' }, 'group.replicator.2.replicator.0.text')).toBe(true);
    expect(showFieldIf({ '$parent.$parent.name': 'Leia' }, 'group.replicator.2.replicator.0.text')).toBe(false);
});

test('it can run conditions on nested data using `root.` without `$` for backwards compatibility', () => {
    setValues(
        {
            name: 'Han',
            address: {
                country: 'Canada',
            },
        },
        'user',
    );

    expect(showFieldIf({ 'root.user.address.country': 'Canada' })).toBe(true);
    expect(showFieldIf({ 'root.user.address.country': 'Australia' })).toBe(false);
});

test('it can run conditions on root store values', () => {
    setStoreValues({
        favorite_foods: ['pizza', 'lasagna', 'asparagus', 'quinoa', 'peppers'],
    });

    expect(showFieldIf({ favorite_foods: 'contains lasagna' })).toBe(false);
    expect(showFieldIf({ '$root.favorite_foods': 'contains lasagna' })).toBe(true);
});

test('it can run conditions on root store values using `root.` without `$` for backwards compatibility', () => {
    setStoreValues({
        favorite_foods: ['pizza', 'lasagna', 'asparagus', 'quinoa', 'peppers'],
    });

    expect(showFieldIf({ 'root.favorite_foods': 'contains lasagna' })).toBe(true);
});

test('it can run conditions on prefixed fields', async () => {
    setValues({
        prefixed_first_name: 'Rincess',
        prefixed_last_name: 'Pleia',
    });

    expect(Fields.showField({ prefix: 'prefixed_', if: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(
        true,
    );
    expect(Fields.showField({ prefix: 'prefixed_', if: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(
        false,
    );
});

test('it can run conditions on nested prefixed fields', async () => {
    setValues(
        {
            prefixed_first_name: 'Rincess',
            prefixed_last_name: 'Pleia',
            prefixed_address: {
                home_planet: 'Elderaan',
            },
        },
        'nested',
    );

    expect(Fields.showField({ prefix: 'prefixed_', if: { first_name: 'is Rincess', last_name: 'is Pleia' } })).toBe(
        true,
    );
    expect(Fields.showField({ prefix: 'prefixed_', if: { first_name: 'is Rincess', last_name: 'is Holo' } })).toBe(
        false,
    );
    expect(Fields.showField({ if: { '$root.nested.prefixed_last_name': 'is Pleia' } })).toBe(true);
    expect(Fields.showField({ if: { '$root.nested.prefixed_last_name': 'is Holo' } })).toBe(false);
    expect(
        Fields.showField({ if: { '$parent.prefixed_last_name': 'is Pleia' } }, 'nested.prefixed_address.home_planet'),
    ).toBe(true);
    expect(
        Fields.showField({ if: { '$parent.prefixed_last_name': 'is Holo' } }, 'nested.prefixed_address.home_planet'),
    ).toBe(false);
});

test('it can call a custom function', () => {
    setValues({
        favorite_animals: ['cats', 'dogs'],
    });

    Statamic.$conditions.add('reallyLovesAnimals', function ({ target, params, values }) {
        expect(target).toBe(null);
        expect(params).toEqual([]);
        return values.favorite_animals.length > 3;
    });

    expect(Fields.showField({ if: 'reallyLovesAnimals' })).toBe(false);
    expect(Fields.showField({ if: 'custom reallyLovesAnimals' })).toBe(false);
    expect(Fields.showField({ unless: 'reallyLovesAnimals' })).toBe(true);
    expect(Fields.showField({ unless: 'custom reallyLovesAnimals' })).toBe(true);
});

test('it can call a custom function that uses `fieldPath` param to evaluate nested fields', () => {
    setValues({
        nested: [{ favorite_animals: ['cats', 'dogs'] }, { favorite_animals: ['cats', 'dogs', 'giraffes', 'lions'] }],
    });

    Statamic.$conditions.add('reallyLovesAnimals', function ({ target, params, root, fieldPath }) {
        expect(target).toBe(null);
        expect(params).toEqual([]);

        return data_get(root, fieldPath).length > 3;
    });

    expect(showFieldIf({ favorite_animals: 'custom reallyLovesAnimals' }, 'nested.0.favorite_animals')).toBe(false);
    expect(showFieldIf({ favorite_animals: 'custom reallyLovesAnimals' }, 'nested.1.favorite_animals')).toBe(true);
});

test('it can call a custom function using params against root values', () => {
    setStoreValues({
        favorite_foods: ['pizza', 'lasagna', 'asparagus', 'quinoa', 'peppers'],
    });

    Statamic.$conditions.add('reallyLoves', function ({ target, params, root }) {
        expect(target).toBe(null);
        return params.filter((food) => !root.favorite_foods.includes(food)).length === 0;
    });

    expect(Fields.showField({ if: 'reallyLoves:lasagna,pizza' })).toBe(true);
    expect(Fields.showField({ if: 'reallyLoves:lasagna,pizza,sandwiches' })).toBe(false);
});

test('it can call a custom function on a specific field', () => {
    setValues({
        favorite_animals: ['cats', 'dogs', 'rats', 'bats'],
    });

    Statamic.$conditions.add('lovesAnimals', function ({ target, params, values, fieldPath }) {
        expect(target).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(values.favorite_animals).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(params).toEqual([]);
        expect(fieldPath).toBe('favorite_animals');
        return values.favorite_animals.length > 3;
    });

    expect(showFieldIf({ favorite_animals: 'custom lovesAnimals' })).toBe(true);
});

test('it can call a custom function on a specific field using params against a root value', () => {
    setStoreValues({
        favorite_animals: ['cats', 'dogs', 'rats', 'bats'],
    });

    Statamic.$conditions.add('lovesAnimals', function ({ target, params, root, fieldPath }) {
        expect(target).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(root.favorite_animals).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(fieldPath).toBe('favorite_animals');
        return target.length > (params[0] || 3);
    });

    expect(showFieldIf({ '$root.favorite_animals': 'custom lovesAnimals' })).toBe(true);
    expect(showFieldIf({ '$root.favorite_animals': 'custom lovesAnimals:2' })).toBe(true);
    expect(showFieldIf({ '$root.favorite_animals': 'custom lovesAnimals:7' })).toBe(false);
});

test('it can call a custom function on a specific field using params against a root value using `root.` backwards compatibility', () => {
    setStoreValues({
        favorite_animals: ['cats', 'dogs', 'rats', 'bats'],
    });

    Statamic.$conditions.add('lovesAnimals', function ({ target, params, root, fieldPath }) {
        expect(target).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(root.favorite_animals).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(fieldPath).toBe('favorite_animals');
        return target.length > (params[0] || 3);
    });

    expect(showFieldIf({ 'root.favorite_animals': 'custom lovesAnimals' })).toBe(true);
    expect(showFieldIf({ 'root.favorite_animals': 'custom lovesAnimals:2' })).toBe(true);
    expect(showFieldIf({ 'root.favorite_animals': 'custom lovesAnimals:7' })).toBe(false);
});

test('it fails if the condition lhs is not evaluatable', () => {
    setValues({
        favorite_animals: [],
    });

    expect(Fields.showField({ if: { favorite_animals: 'not null' } })).toBe(false);
    expect(Fields.showField({ unless: { favorite_animals: 'not null' } })).toBe(true);
});

test('it can mix custom and non-custom conditions', () => {
    setValues({
        first_name: 'San',
        last_name: 'Holo',
        age: 22,
    });

    Statamic.$conditions.add('isOlderThan', function ({ target, params }) {
        return target > params[0];
    });

    Statamic.$conditions.add('startsWith', function ({ target, params }) {
        return target[0].toLowerCase() === params[0];
    });

    expect(showFieldIf({ first_name: 'is San', last_name: 'custom startsWith:h', age: 'custom isOlderThan:16' })).toBe(
        true,
    );
    expect(
        showFieldIf({ first_name: 'is Feedo', last_name: 'custom startsWith:h', age: 'custom isOlderThan:16' }),
    ).toBe(false);
    expect(showFieldIf({ first_name: 'is San', last_name: 'custom startsWith:h', age: 'custom isOlderThan:40' })).toBe(
        false,
    );
    expect(showFieldIf({ first_name: 'is San', last_name: 'custom startsWith:z', age: 'custom isOlderThan:16' })).toBe(
        false,
    );
    expect(showFieldIf({ first_name: 'is San', last_name: 'custom startsWith:z', age: 'custom isOlderThan:40' })).toBe(
        false,
    );
});

test('it can externally force hide a field before validator conditions are evaluated', () => {
    setValues({ first_name: 'Jesse' });

    expect(Fields.showField({ handle: 'some_field' })).toBe(true);
    expect(Fields.showField({ handle: 'last_name', if: { first_name: 'Jesse' } })).toBe(true);

    setHiddenField({
        dottedKey: 'last_name',
        hidden: 'force',
        omitValue: false,
    });

    setHiddenField({
        dottedKey: 'some_field',
        hidden: 'force',
        omitValue: false,
    });

    expect(Fields.showField({ handle: 'some_field' })).toBe(false);
    expect(Fields.showField({ handle: 'last_name', if: { first_name: 'Jesse' } })).toBe(false);
});

test('it never omits fields with always_save config', async () => {
    setValues({
        is_online_event: false,
        venue: false,
    });

    await setHiddenFieldsState([
        { handle: 'is_online_event' },
        { handle: 'venue', if: { is_online_event: true }, always_save: true },
    ]);

    expect(Store.hiddenFields['is_online_event'].hidden).toBe(false);
    expect(Store.hiddenFields['venue'].hidden).toBe(true);
    expect(Store.hiddenFields['is_online_event'].omitValue).toBe(false);
    expect(Store.hiddenFields['venue'].omitValue).toBe(false);
});

test('it never omits nested fields with always_save config', async () => {
    setValues(
        {
            is_online_event: false,
            venue: false,
        },
        'nested',
    );

    await setHiddenFieldsState(
        [{ handle: 'is_online_event' }, { handle: 'venue', if: { is_online_event: true }, always_save: true }],
        'nested',
    );

    expect(Store.hiddenFields['nested.is_online_event'].hidden).toBe(false);
    expect(Store.hiddenFields['nested.venue'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.is_online_event'].omitValue).toBe(false);
    expect(Store.hiddenFields['nested.venue'].omitValue).toBe(false);
});

test('it force hides fields with hidden visibility config', async () => {
    await setHiddenFieldsState([{ handle: 'first_name' }, { handle: 'last_name', visibility: 'hidden' }]);

    expect(Store.hiddenFields['first_name'].hidden).toBe(false);
    expect(Store.hiddenFields['last_name'].hidden).toBe('force');
    expect(Store.hiddenFields['first_name'].omitValue).toBe(false);
    expect(Store.hiddenFields['last_name'].omitValue).toBe(false);
});

test('it tells omitter to omit hidden fields by default', async () => {
    setValues({
        is_online_event: false,
        venue: false,
    });

    await setHiddenFieldsState([{ handle: 'is_online_event' }, { handle: 'venue', if: { is_online_event: true } }]);

    expect(Store.hiddenFields['is_online_event'].hidden).toBe(false);
    expect(Store.hiddenFields['venue'].hidden).toBe(true);
    expect(Store.hiddenFields['is_online_event'].omitValue).toBe(false);
    expect(Store.hiddenFields['venue'].omitValue).toBe(true);
});

test('it tells omitter to omit nested hidden fields by default', async () => {
    setValues(
        {
            is_online_event: false,
            venue: false,
        },
        'nested',
    );

    await setHiddenFieldsState(
        [{ handle: 'is_online_event' }, { handle: 'venue', if: { is_online_event: true } }],
        'nested',
    );

    expect(Store.hiddenFields['nested.is_online_event'].hidden).toBe(false);
    expect(Store.hiddenFields['nested.venue'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.is_online_event'].omitValue).toBe(false);
    expect(Store.hiddenFields['nested.venue'].omitValue).toBe(true);
});

test('it tells omitter to omit revealer fields', async () => {
    setValues({
        revealer_toggle: false,
        regular_toggle: false,
    });

    await setHiddenFieldsState([
        { handle: 'revealer_toggle', type: 'revealer' },
        { handle: 'regular_toggle', type: 'regular' },
    ]);

    expect(Store.hiddenFields['revealer_toggle'].hidden).toBe(false);
    expect(Store.hiddenFields['regular_toggle'].hidden).toBe(false);
    expect(Store.hiddenFields['revealer_toggle'].omitValue).toBe(true);
    expect(Store.hiddenFields['regular_toggle'].omitValue).toBe(false);
});

test('it tells omitter to omit nested revealer fields', async () => {
    setValues(
        {
            revealer_toggle: false,
            regular_toggle: false,
        },
        'nested',
    );

    await setHiddenFieldsState(
        [
            { handle: 'revealer_toggle', type: 'revealer' },
            { handle: 'regular_toggle', type: 'regular' },
        ],
        'nested',
    );

    expect(Store.hiddenFields['nested.revealer_toggle'].hidden).toBe(false);
    expect(Store.hiddenFields['nested.regular_toggle'].hidden).toBe(false);
    expect(Store.hiddenFields['nested.revealer_toggle'].omitValue).toBe(true);
    expect(Store.hiddenFields['nested.regular_toggle'].omitValue).toBe(false);
});

test('it tells omitter not omit revealer-hidden fields', async () => {
    setValues({
        show_more_info: false,
        venue: false,
    });

    await setHiddenFieldsState([
        { handle: 'show_more_info', type: 'revealer' },
        { handle: 'venue', if: { show_more_info: true } },
    ]);

    expect(Store.hiddenFields['show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['venue'].hidden).toBe(true);
    expect(Store.hiddenFields['show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['venue'].omitValue).toBe(false);
});

test('it tells omitter not omit revealer-hidden fields using root syntax in condition', async () => {
    setValues({
        show_more_info: false,
        venue: false,
    });

    await setHiddenFieldsState([
        { handle: 'show_more_info', type: 'revealer' },
        { handle: 'venue', if: { '$root.show_more_info': true } },
    ]);

    expect(Store.hiddenFields['show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['venue'].hidden).toBe(true);
    expect(Store.hiddenFields['show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['venue'].omitValue).toBe(false);
});

test('it tells omitter not omit revealer-hidden fields using legacy root syntax for backwards compatibility', async () => {
    setValues({
        show_more_info: false,
        venue: false,
    });

    await setHiddenFieldsState([
        { handle: 'show_more_info', type: 'revealer' },
        { handle: 'venue', if: { 'root.show_more_info': true } },
    ]);

    expect(Store.hiddenFields['show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['venue'].hidden).toBe(true);
    expect(Store.hiddenFields['show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['venue'].omitValue).toBe(false);
});

test('it tells omitter not omit nested revealer-hidden fields', async () => {
    setValues(
        {
            show_more_info: false,
            venue: false,
        },
        'nested',
    );

    await setHiddenFieldsState(
        [
            { handle: 'show_more_info', type: 'revealer' },
            { handle: 'venue', if: { show_more_info: true } },
        ],
        'nested',
    );

    expect(Store.hiddenFields['nested.show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['nested.venue'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['nested.venue'].omitValue).toBe(false);
});

test('it tells omitter not omit nested revealer-hidden fields using root syntax in condition', async () => {
    setValues(
        {
            show_more_info: false,
            venue: false,
        },
        'nested',
    );

    await setHiddenFieldsState(
        [
            { handle: 'show_more_info', type: 'revealer' },
            { handle: 'venue', if: { '$root.nested.show_more_info': true } },
        ],
        'nested',
    );

    expect(Store.hiddenFields['nested.show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['nested.venue'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['nested.venue'].omitValue).toBe(false);
});

test('it tells omitter not omit nested revealer-hidden fields using legacy root syntax for backwards compatibility', async () => {
    setValues(
        {
            show_more_info: false,
            venue: false,
        },
        'nested',
    );

    await setHiddenFieldsState(
        [
            { handle: 'show_more_info', type: 'revealer' },
            { handle: 'venue', if: { 'root.nested.show_more_info': true } },
        ],
        'nested',
    );

    expect(Store.hiddenFields['nested.show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['nested.venue'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['nested.venue'].omitValue).toBe(false);
});

test('it tells omitter not omit nested revealer-hidden fields using parent syntax in condition', async () => {
    setValues({
        top_level_show_more_info: false,
        replicator: [{ text: 'Foo' }, { text: 'Bar' }],
        group: {
            show_more_info: false,
            replicator: [
                { text: 'Foo' },
                { text: 'Bar' },
                {
                    show_more_info: false,
                    replicator: [{ text: 'Foo' }],
                    group: {
                        show_more_info: false,
                        replicator: [{ text: 'Foo' }],
                    },
                },
            ],
        },
    });

    // Track revealer toggles
    await setHiddenFieldsState([
        { handle: 'top_level_show_more_info', type: 'revealer' },
        { handle: 'group.show_more_info', type: 'revealer' },
        { handle: 'group.replicator.2.show_more_info', type: 'revealer' },
        { handle: 'group.replicator.2.group.show_more_info', type: 'revealer' },
    ]);

    // Set revealer hidden fields using `$parent` syntax
    await setHiddenFieldsState([
        { handle: 'replicator.1.text', if: { '$parent.top_level_show_more_info': true } },
        { handle: 'group.replicator.1.text', if: { '$parent.show_more_info': true } },
    ]);

    // Set revealer hidden fields using chained `$parent` syntax
    await setHiddenFieldsState([
        {
            handle: 'group.replicator.2.replicator.0.text',
            if: { '$parent.$parent.$parent.top_level_show_more_info': true },
        },
        {
            handle: 'group.replicator.2.group.replicator.0.text',
            if: { '$parent.$parent.$parent.$parent.top_level_show_more_info': true },
        },
    ]);

    // Ensure revealer toggles should definitely hidden and omited from submitted payload
    expect(Store.hiddenFields['top_level_show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['top_level_show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['group.show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['group.show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['group.replicator.2.show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['group.replicator.2.show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['group.replicator.2.group.show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['group.replicator.2.group.show_more_info'].omitValue).toBe(true);

    // Ensure revealer hidden fields should be hiddden, but not omitted
    expect(Store.hiddenFields['replicator.1.text'].hidden).toBe(true);
    expect(Store.hiddenFields['replicator.1.text'].omitValue).toBe(false);
    expect(Store.hiddenFields['group.replicator.1.text'].hidden).toBe(true);
    expect(Store.hiddenFields['group.replicator.1.text'].omitValue).toBe(false);
    expect(Store.hiddenFields['group.replicator.2.replicator.0.text'].hidden).toBe(true);
    expect(Store.hiddenFields['group.replicator.2.replicator.0.text'].omitValue).toBe(false);
    expect(Store.hiddenFields['group.replicator.2.group.replicator.0.text'].hidden).toBe(true);
    expect(Store.hiddenFields['group.replicator.2.group.replicator.0.text'].omitValue).toBe(false);

    // Just a few extra assertions to ensure only sets with revealer conditions should be affected
    expect('replicator.0.text' in Store.hiddenFields).toBe(false);
    expect('group.replicator.0.text' in Store.hiddenFields).toBe(false);
});

test('it tells omitter not omit prefixed revealer-hidden fields', async () => {
    setValues({
        prefixed_show_more_info: false,
        prefixed_event_venue: false,
    });

    await setHiddenFieldsState([
        { handle: 'prefixed_show_more_info', prefix: 'prefixed_', type: 'revealer' },
        { handle: 'prefixed_venue', prefix: 'prefixed_', if: { show_more_info: true } },
    ]);

    expect(Store.hiddenFields['prefixed_show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['prefixed_venue'].hidden).toBe(true);
    expect(Store.hiddenFields['prefixed_show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['prefixed_venue'].omitValue).toBe(false);
});

test('it tells omitter not omit nested prefixed revealer-hidden fields', async () => {
    setValues(
        {
            prefixed_show_more_info: false,
            prefixed_event_venue: false,
        },
        'nested',
    );

    await setHiddenFieldsState(
        [
            { handle: 'prefixed_show_more_info', prefix: 'prefixed_', type: 'revealer' },
            { handle: 'prefixed_venue', prefix: 'prefixed_', if: { show_more_info: true } },
        ],
        'nested',
    );

    expect(Store.hiddenFields['nested.prefixed_show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['nested.prefixed_venue'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.prefixed_show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['nested.prefixed_venue'].omitValue).toBe(false);
});

test('it properly omits revealer-hidden fields when multiple conditions are set', async () => {
    setValues({
        show_more_info: false,
        has_second_event_venue: true,
        has_third_event_venue: false,
        event_venue_one: 'Stadium One',
        event_venue_two: 'Stadium Two',
        event_venue_three: false,
    });

    await setHiddenFieldsState([
        { handle: 'show_more_info', type: 'revealer' },
        { handle: 'has_second_event_venue', type: 'toggle', if: { show_more_info: true } },
        { handle: 'has_third_event_venue', type: 'toggle', if: { show_more_info: true } },
        { handle: 'event_venue_one', if: { show_more_info: true } },
        { handle: 'event_venue_two', if: { show_more_info: true, has_second_event_venue: true } },
        { handle: 'event_venue_three', if: { show_more_info: true, has_third_event_venue: true } },
    ]);

    expect(Store.hiddenFields['show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['has_second_event_venue'].hidden).toBe(true);
    expect(Store.hiddenFields['has_third_event_venue'].hidden).toBe(true);
    expect(Store.hiddenFields['event_venue_one'].hidden).toBe(true);
    expect(Store.hiddenFields['event_venue_two'].hidden).toBe(true);
    expect(Store.hiddenFields['event_venue_three'].hidden).toBe(true);

    expect(Store.hiddenFields['show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['has_second_event_venue'].omitValue).toBe(false);
    expect(Store.hiddenFields['has_third_event_venue'].omitValue).toBe(false);
    expect(Store.hiddenFields['event_venue_one'].omitValue).toBe(false);
    expect(Store.hiddenFields['event_venue_two'].omitValue).toBe(false);

    // Though this third venue is hidden by a revealer, it's also disabled by a regular toggle condition, so it should actually be omitted...
    expect(Store.hiddenFields['event_venue_three'].omitValue).toBe(true);
});

test('it properly omits nested revealer-hidden fields when multiple conditions are set', async () => {
    setValues(
        {
            show_more_info: false,
            has_second_event_venue: true,
            has_third_event_venue: false,
            event_venue_one: 'Stadium One',
            event_venue_two: 'Stadium Two',
            event_venue_three: false,
        },
        'nested',
    );

    await setHiddenFieldsState(
        [
            { handle: 'show_more_info', type: 'revealer' },
            { handle: 'has_second_event_venue', type: 'toggle', if: { show_more_info: true } },
            { handle: 'has_third_event_venue', type: 'toggle', if: { show_more_info: true } },
            { handle: 'event_venue_one', if: { show_more_info: true } },
            { handle: 'event_venue_two', if: { show_more_info: true, has_second_event_venue: true } },
            { handle: 'event_venue_three', if: { show_more_info: true, has_third_event_venue: true } },
        ],
        'nested',
    );

    expect(Store.hiddenFields['nested.show_more_info'].hidden).toBe(false);
    expect(Store.hiddenFields['nested.has_second_event_venue'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.has_third_event_venue'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.event_venue_one'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.event_venue_two'].hidden).toBe(true);
    expect(Store.hiddenFields['nested.event_venue_three'].hidden).toBe(true);

    expect(Store.hiddenFields['nested.show_more_info'].omitValue).toBe(true);
    expect(Store.hiddenFields['nested.has_second_event_venue'].omitValue).toBe(false);
    expect(Store.hiddenFields['nested.has_third_event_venue'].omitValue).toBe(false);
    expect(Store.hiddenFields['nested.event_venue_one'].omitValue).toBe(false);
    expect(Store.hiddenFields['nested.event_venue_two'].omitValue).toBe(false);

    // Though this third venue is hidden by a revealer, it's also disabled by a regular toggle condition, so it should actually be omitted...
    expect(Store.hiddenFields['nested.event_venue_three'].omitValue).toBe(true);
});

test('it can use extra values in conditions', () => {
    setValues({});
    setExtraValues({ hello: 'world' });

    expect(showFieldIf({ hello: 'world' })).toBe(true);
    expect(showFieldIf({ hello: 'there' })).toBe(false);
});
