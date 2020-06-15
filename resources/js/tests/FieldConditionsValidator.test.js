import Vue from 'vue';
import Vuex from 'vuex';
import ValidatesFieldConditions from '../components/field-conditions/ValidatorMixin.js';
require('../bootstrap/globals');
global._ = require('underscore');
Vue.use(Vuex);

const Store = new Vuex.Store({
    state: {
        publish: {base: {values: {}}},
        statamic: {conditions: {}}
    },
    mutations: {
        setValues(state, values) {
            state.publish.base.values = values;
        },
        setCondition(state, payload) {
            state.statamic.conditions[payload.name] = payload.condition;
        }
    }
});

const Statamic = {
    $conditions: {
        add: (name, condition) => Store.commit('setCondition', {name, condition})
    }
};

const Fields = new Vue({
    mixins: [ValidatesFieldConditions],
    store: Store,
    data() {
        return {
            storeName: 'base',
            values: {}
        }
    },
    methods: {
        setValues(values) {
            this.values = values;
            Store.commit('setValues', values);
        },
        setStoreValues(values) {
            Store.commit('setValues', values);
        }
    }
});

let showFieldIf = function (conditions=null) {
    return Fields.showField(conditions ? {'if': conditions} : {});
};

afterEach(() => {
    Fields.values = {};
});

test('it shows field by default', () => {
    expect(showFieldIf()).toBe(true);
});

test('it shows or hides field based on shorthand equals conditions', () => {
    Fields.setValues({first_name: 'Jesse'});

    expect(showFieldIf({first_name: 'Jesse'})).toBe(true);
    expect(showFieldIf({first_name: 'Jack'})).toBe(false);
});

test('it can use comparison operators in conditions', () => {
    Fields.setValues({
        last_name: 'Hasselhoff',
        age: 13,
        string_age: "3"
    });

    expect(showFieldIf({age: '== 13'})).toBe(true);
    expect(showFieldIf({age: '!= 5'})).toBe(true);
    expect(showFieldIf({last_name: '=== Hasselhoff'})).toBe(true);
    expect(showFieldIf({last_name: '!== Fischer'})).toBe(true);
    expect(showFieldIf({age: '=== 13'})).toBe(false); // We don't cast their condition on strict equality comparisons

    expect(showFieldIf({age: '> 5'})).toBe(true);
    expect(showFieldIf({age: '> 13'})).toBe(false);
    expect(showFieldIf({age: '> 20'})).toBe(false);
    expect(showFieldIf({age: '>= 13'})).toBe(true);

    expect(showFieldIf({age: '< 5'})).toBe(false);
    expect(showFieldIf({age: '< 13'})).toBe(false);
    expect(showFieldIf({age: '< 20'})).toBe(true);
    expect(showFieldIf({age: '<= 13'})).toBe(true);
    expect(showFieldIf({string_age: '<= 13'})).toBe(true); // We cast to number when doing greater/less than comparisons

    expect(showFieldIf({age: 'is 13'})).toBe(true);
    expect(showFieldIf({age: 'equals 13'})).toBe(true);
    expect(showFieldIf({age: 'not 13'})).toBe(false);
    expect(showFieldIf({age: 'isnt 13'})).toBe(false);
    expect(showFieldIf({age: '¯\\_(ツ)_/¯ 13'})).toBe(false);
});

test('it can use includes or contains operators in conditions', () => {
    Fields.setValues({
        cancellation_reasons: [
            'found another service',
            'other'
        ],
        example_string: 'The quick brown fox jumps over the lazy dog',
        age: 13,
        empty_string: '',
        null_value: null,
    });

    expect(showFieldIf({cancellation_reasons: 'includes other'})).toBe(true);
    expect(showFieldIf({cancellation_reasons: 'contains other'})).toBe(true);
    expect(showFieldIf({cancellation_reasons: 'includes slow service'})).toBe(false);
    expect(showFieldIf({cancellation_reasons: 'contains slow service'})).toBe(false);

    expect(showFieldIf({example_string: 'includes fox jumps'})).toBe(true);
    expect(showFieldIf({example_string: 'contains fox jumps'})).toBe(true);
    expect(showFieldIf({example_string: 'includes dog jumps'})).toBe(false);
    expect(showFieldIf({example_string: 'contains dog jumps'})).toBe(false);

    expect(showFieldIf({age: 'includes 13'})).toBe(true);
    expect(showFieldIf({age: 'contains 13'})).toBe(true);
    expect(showFieldIf({age: 'includes fox'})).toBe(false);
    expect(showFieldIf({age: 'contains fox'})).toBe(false);

    expect(showFieldIf({empty_string: 'contains fox'})).toBe(false);
    expect(showFieldIf({null_value: 'contains fox'})).toBe(false);
});

test('it can use includes_any or contains_any operators in conditions', () => {
    Fields.setValues({
        cancellation_reasons: [
            'found another service',
            'other'
        ],
        example_string: 'The quick brown fox jumps over the lazy dog',
        age: 13,
        empty_string: '',
        null_value: null,
    });

    expect(showFieldIf({cancellation_reasons: 'includes_any sick, other'})).toBe(true);
    expect(showFieldIf({cancellation_reasons: 'contains_any sick, other'})).toBe(true);
    expect(showFieldIf({cancellation_reasons: 'includes_any sick, found another'})).toBe(false);
    expect(showFieldIf({cancellation_reasons: 'contains_any sick, found another'})).toBe(false);

    expect(showFieldIf({example_string: 'includes_any parrot, lazy dog'})).toBe(true);
    expect(showFieldIf({example_string: 'contains_any parrot, lazy dog'})).toBe(true);
    expect(showFieldIf({example_string: 'includes_any parrot, hops'})).toBe(false);
    expect(showFieldIf({example_string: 'contains_any parrot, hops'})).toBe(false);

    expect(showFieldIf({age: 'includes_any fox, 13'})).toBe(true);
    expect(showFieldIf({age: 'contains_any fox, 13'})).toBe(true);
    expect(showFieldIf({age: 'includes_any fox, 14'})).toBe(false);
    expect(showFieldIf({age: 'contains_any fox, 14'})).toBe(false);

    expect(showFieldIf({empty_string: 'contains_any fox, 13'})).toBe(false);
    expect(showFieldIf({null_value: 'contains_any fox, 13'})).toBe(false);
});

test('it handles null, true, and false in condition as literal', () => {
    Fields.setValues({
        last_name: 'HasselHoff',
        likes_food: true,
        likes_animals: false,
        favorite_animal: null,
        not_real_boolean: 'false'
    });

    expect(showFieldIf({first_name: '=== null'})).toBe(true);
    expect(showFieldIf({last_name: '!== null'})).toBe(true);
    expect(showFieldIf({likes_food: '=== true'})).toBe(true);
    expect(showFieldIf({likes_animals: '=== false'})).toBe(true);
    expect(showFieldIf({favorite_animal: '=== null'})).toBe(true);
    expect(showFieldIf({not_real_boolean: '=== false'})).toBe(false);
});

test('it can check if value is empty', () => {
    Fields.setValues({
        last_name: 'HasselHoff',
        user: {email: 'david@hasselhoff.com'},
        favorite_foods: ['lasagna'],
        empty_string: '',
        empty_array: [],
        empty_object: {},
    });

    expect(showFieldIf({first_name: 'empty'})).toBe(true);
    expect(showFieldIf({last_name: 'is empty'})).toBe(false);
    expect(showFieldIf({last_name: 'isnt empty'})).toBe(true);
    expect(showFieldIf({last_name: 'not empty'})).toBe(true);
    expect(showFieldIf({user: 'empty'})).toBe(false);
    expect(showFieldIf({favorite_foods: 'empty'})).toBe(false);
    expect(showFieldIf({empty_string: 'empty'})).toBe(true);
    expect(showFieldIf({empty_array: 'empty'})).toBe(true);
    expect(showFieldIf({empty_object: 'empty'})).toBe(true);
});

test('it can use operators with multi-word values', () => {
    Fields.setValues({ace_ventura_says: 'Allllllrighty then!'});

    expect(showFieldIf({ace_ventura_says: 'Allllllrighty then!'})).toBe(true);
    expect(showFieldIf({ace_ventura_says: '== Allllllrighty then!'})).toBe(true);
    expect(showFieldIf({ace_ventura_says: 'is Allllllrighty then!'})).toBe(true);
    expect(showFieldIf({ace_ventura_says: 'not I am your father'})).toBe(true);
});

test('it only shows when multiple conditions are met', () => {
    Fields.setValues({
        first_name: 'San',
        last_name: 'Holo',
        age: 22
    });

    expect(showFieldIf({first_name: 'is San', last_name: 'is Holo', age: '!= 20'})).toBe(true);
    expect(showFieldIf({first_name: 'is San', last_name: 'is Holo', age: '> 40'})).toBe(false);
});

test('it shows or hides with parent key variants', () => {
    Fields.setValues({
        first_name: 'Rincess',
        last_name: 'Pleia'
    });

    expect(Fields.showField({if: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(true);
    expect(Fields.showField({if: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(false);

    expect(Fields.showField({show_when: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(true);
    expect(Fields.showField({show_when: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(false);

    expect(Fields.showField({unless: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(false);
    expect(Fields.showField({unless: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(true);

    expect(Fields.showField({hide_when: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(false);
    expect(Fields.showField({hide_when: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(true);
});

test('it shows or hides when any of the conditions are met', () => {
    Fields.setValues({
        first_name: 'Rincess',
        last_name: 'Pleia'
    });

    expect(Fields.showField({if_any: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(true);
    expect(Fields.showField({if_any: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(true);
    expect(Fields.showField({if_any: {first_name: 'is San', last_name: 'is Holo'}})).toBe(false);

    expect(Fields.showField({show_when_any: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(true);
    expect(Fields.showField({show_when_any: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(true);
    expect(Fields.showField({show_when_any: {first_name: 'is San', last_name: 'is Holo'}})).toBe(false);

    expect(Fields.showField({unless_any: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(false);
    expect(Fields.showField({unless_any: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(false);
    expect(Fields.showField({unless_any: {first_name: 'is San', last_name: 'is Holo'}})).toBe(true);

    expect(Fields.showField({hide_when_any: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(false);
    expect(Fields.showField({hide_when_any: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(false);
    expect(Fields.showField({hide_when_any: {first_name: 'is San', last_name: 'is Holo'}})).toBe(true);
});

test('it can run conditions on nested data', () => {
    Fields.setValues({
        user: {
            address: {
                country: 'Canada'
            }
        }
    });

    expect(showFieldIf({'user.address.country': 'Canada'})).toBe(true);
    expect(showFieldIf({'user.address.country': 'Australia'})).toBe(false);
});

test('it can run conditions on root store values', () => {
    Fields.setStoreValues({
        favorite_foods: ['pizza', 'lasagna', 'asparagus', 'quinoa', 'peppers'],
    });

    expect(showFieldIf({'favorite_foods': 'contains lasagna'})).toBe(false);
    expect(showFieldIf({'root.favorite_foods': 'contains lasagna'})).toBe(true);
});

test('it can call a custom function', () => {
    Fields.setValues({
        favorite_animals: ['cats', 'dogs'],
    });

    Statamic.$conditions.add('reallyLovesAnimals', function ({ target, params, store, storeName, values }) {
        expect(target).toBe(null);
        expect(params).toEqual([]);
        expect(store).toBe(Store);
        expect(storeName).toBe('base');
        return values.favorite_animals.length > 3;
    });

    expect(Fields.showField({if: 'reallyLovesAnimals'})).toBe(false);
    expect(Fields.showField({unless: 'reallyLovesAnimals'})).toBe(true);
});

test('it can call a custom function using params against root values', () => {
    Fields.setStoreValues({
        favorite_foods: ['pizza', 'lasagna', 'asparagus', 'quinoa', 'peppers'],
    });

    Statamic.$conditions.add('reallyLoves', function ({ target, params, store, storeName, root }) {
        expect(target).toBe(null);
        expect(store).toBe(Store);
        expect(storeName).toBe('base');
        return params.filter(food => ! root.favorite_foods.includes(food)).length === 0;
    });

    expect(Fields.showField({if: 'reallyLoves:lasagna,pizza'})).toBe(true);
    expect(Fields.showField({if: 'reallyLoves:lasagna,pizza,sandwiches'})).toBe(false);
});

test('it can call a custom function on a specific field', () => {
    Fields.setValues({
        favorite_animals: ['cats', 'dogs', 'rats', 'bats'],
    });

    Statamic.$conditions.add('lovesAnimals', function ({ target, params, store, storeName, values }) {
        expect(target).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(values.favorite_animals).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(params).toEqual([]);
        expect(store).toBe(Store);
        expect(storeName).toBe('base');
        return values.favorite_animals.length > 3;
    });

    expect(showFieldIf({'favorite_animals': 'custom lovesAnimals'})).toBe(true);
});

test('it can call a custom function on a specific field using params against a root value', () => {
    Fields.setStoreValues({
        favorite_animals: ['cats', 'dogs', 'rats', 'bats'],
    });

    Statamic.$conditions.add('lovesAnimals', function ({ target, params, store, storeName, root }) {
        expect(target).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(root.favorite_animals).toEqual(['cats', 'dogs', 'rats', 'bats']);
        expect(store).toBe(Store);
        expect(storeName).toBe('base');
        return target.length > (params[0] || 3);
    });

    expect(showFieldIf({'root.favorite_animals': 'custom lovesAnimals'})).toBe(true);
    expect(showFieldIf({'root.favorite_animals': 'custom lovesAnimals:2'})).toBe(true);
    expect(showFieldIf({'root.favorite_animals': 'custom lovesAnimals:7'})).toBe(false);
});

test('it can mix custom and non-custom conditions', () => {
    Fields.setValues({
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

    expect(showFieldIf({first_name: 'is San', last_name: 'custom startsWith:h', age: 'custom isOlderThan:16'})).toBe(true);
    expect(showFieldIf({first_name: 'is Feedo', last_name: 'custom startsWith:h', age: 'custom isOlderThan:16'})).toBe(false);
    expect(showFieldIf({first_name: 'is San', last_name: 'custom startsWith:h', age: 'custom isOlderThan:40'})).toBe(false);
    expect(showFieldIf({first_name: 'is San', last_name: 'custom startsWith:z', age: 'custom isOlderThan:16'})).toBe(false);
    expect(showFieldIf({first_name: 'is San', last_name: 'custom startsWith:z', age: 'custom isOlderThan:40'})).toBe(false);
});

// TODO: Implement wildcards using asterisks? Is this useful?
// test('it can run conditions on nested data using wildcards', () => {
//     Fields.setValues({
//         related_posts: [
//             {title: 'Learning Laravel', slug: 'learning-laravel'},
//             {title: 'Learning Vue', slug: 'learning-vue'},
//         ]
//     });

//     expect(showFieldIf({'related_posts.*.title': 'Learning Vue'})).toBe(true);
//     expect(showFieldIf({'related_posts.*.title': 'Learning Vim'})).toBe(false);
// });
