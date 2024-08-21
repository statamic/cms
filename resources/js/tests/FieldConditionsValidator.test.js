import Vue from 'vue';
import Vuex from 'vuex';
import ValidatesFieldConditions from '../components/field-conditions/ValidatorMixin.js';
Vue.use(Vuex);

const Store = new Vuex.Store({
    modules: {
        statamic: {
            namespaced: true,
            state: {
                conditions: {},
            },
            mutations: {
                setCondition(state, payload) {
                    state.conditions[payload.name] = payload.condition;
                },
            },
        },
        publish: {
            namespaced: true,
            modules: {
                base: {
                    namespaced: true,
                    state: {
                        values: {},
                        hiddenFields: {},
                        revealerFields: [],
                    },
                    mutations: {
                        setValues(state, values) {
                            state.values = values;
                        },
                        setHiddenField(state, field) {
                            state.hiddenFields[field.dottedKey] = {
                                hidden: field.hidden,
                                omitValue: field.omitValue,
                            };
                        },
                        setRevealerField(state, dottedKey) {
                            state.revealerFields.push(dottedKey);
                        },
                        reset(state) {
                            state.values = {};
                            state.hiddenFields = {};
                            state.revealerFields = [];
                        },
                    }
                }
            }
        }
    },
});

const Statamic = {
    $conditions: {
        add: (name, condition) => Store.commit('statamic/setCondition', {name, condition})
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
        setValues(values, nestedKey) {
            this.values = values;
            let storeValues = {};
            if (nestedKey) {
                storeValues[nestedKey] = values;
            } else {
                storeValues = values;
            }
            Store.commit('publish/base/setValues', storeValues);
        },
        setStoreValues(values) {
            Store.commit('publish/base/setValues', values);
        },
        setHiddenField(payload) {
            Store.commit('publish/base/setHiddenField', payload);
        },
        setHiddenFieldsState: async (fieldConfigs, dottedPrefix) => {
            fieldConfigs.filter(fieldConfig => fieldConfig.type === 'revealer').forEach(fieldConfig => {
                Store.commit('publish/base/setRevealerField', dottedPrefix ? `${dottedPrefix}.${fieldConfig.handle}`: fieldConfig.handle)
            });
            fieldConfigs.forEach(fieldConfig => {
                Fields.showField(fieldConfig, dottedPrefix ? `${dottedPrefix}.${fieldConfig.handle}`: null)
            });
            await Vue.nextTick();
        },
    }
});

let showFieldIf = function (conditions=null) {
    return Fields.showField(conditions ? {'if': conditions} : {});
};

afterEach(() => {
    Fields.values = {};
    Store.commit('publish/base/reset');
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
        name: 'Han',
        address: {
            country: 'Canada'
        }
    }, 'user');

    expect(showFieldIf({'name': 'Han'})).toBe(true);
    expect(showFieldIf({'name': 'Chewy'})).toBe(false);
    expect(showFieldIf({'address.country': 'Canada'})).toBe(true);
    expect(showFieldIf({'address.country': 'Australia'})).toBe(false);
    expect(showFieldIf({'root.user.address.country': 'Canada'})).toBe(true);
    expect(showFieldIf({'root.user.address.country': 'Australia'})).toBe(false);
});

test('it can run conditions on root store values', () => {
    Fields.setStoreValues({
        favorite_foods: ['pizza', 'lasagna', 'asparagus', 'quinoa', 'peppers'],
    });

    expect(showFieldIf({'favorite_foods': 'contains lasagna'})).toBe(false);
    expect(showFieldIf({'root.favorite_foods': 'contains lasagna'})).toBe(true);
});

test('it can run conditions on prefixed fields', async () => {
    Fields.setValues({
        prefixed_first_name: 'Rincess',
        prefixed_last_name: 'Pleia'
    });

    expect(Fields.showField({prefix: 'prefixed_', if: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(true);
    expect(Fields.showField({prefix: 'prefixed_', if: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(false);
});

test('it can run conditions on nested prefixed fields', async () => {
    Fields.setValues({
        prefixed_first_name: 'Rincess',
        prefixed_last_name: 'Pleia'
    }, 'nested');

    expect(Fields.showField({prefix: 'prefixed_', if: {first_name: 'is Rincess', last_name: 'is Pleia'}})).toBe(true);
    expect(Fields.showField({prefix: 'prefixed_', if: {first_name: 'is Rincess', last_name: 'is Holo'}})).toBe(false);
    expect(Fields.showField({if: {'root.nested.prefixed_last_name': 'is Pleia'}})).toBe(true);
    expect(Fields.showField({if: {'root.nested.prefixed_last_name': 'is Holo'}})).toBe(false);
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
    expect(Fields.showField({if: 'custom reallyLovesAnimals'})).toBe(false);
    expect(Fields.showField({unless: 'reallyLovesAnimals'})).toBe(true);
    expect(Fields.showField({unless: 'custom reallyLovesAnimals'})).toBe(true);
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

test('it fails if the condition lhs is not evaluatable', () => {
    Fields.setValues({
        favorite_animals: [],
    });

    expect(Fields.showField({if: {'favorite_animals': 'not null'}})).toBe(false);
    expect(Fields.showField({unless: {'favorite_animals': 'not null'}})).toBe(true);
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

test('it can externally force hide a field before validator conditions are evaluated', () => {
    Fields.setValues({first_name: 'Jesse'});

    expect(Fields.showField({handle: 'some_field'})).toBe(true);
    expect(Fields.showField({handle: 'last_name', if: {first_name: 'Jesse'}})).toBe(true);

    Fields.setHiddenField({
        dottedKey: 'last_name',
        hidden: 'force',
        omitValue: false,
    });

    Fields.setHiddenField({
        dottedKey: 'some_field',
        hidden: 'force',
        omitValue: false,
    });

    expect(Fields.showField({handle: 'some_field'})).toBe(false);
    expect(Fields.showField({handle: 'last_name', if: {first_name: 'Jesse'}})).toBe(false);
});

test('it never omits fields with always_save config', async () => {
    Fields.setValues({
        is_online_event: false,
        venue: false,
    });

    await Fields.setHiddenFieldsState([
        {handle: 'is_online_event'},
        {handle: 'venue', if: {is_online_event: true}, always_save: true},
    ]);

    expect(Store.state.publish.base.hiddenFields['is_online_event'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['is_online_event'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['venue'].omitValue).toBe(false);
});

test('it never omits nested fields with always_save config', async () => {
    Fields.setValues({
        is_online_event: false,
        venue: false,
    }, 'nested');

    await Fields.setHiddenFieldsState([
        {handle: 'is_online_event'},
        {handle: 'venue', if: {is_online_event: true}, always_save: true},
    ], 'nested');

    expect(Store.state.publish.base.hiddenFields['nested.is_online_event'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.is_online_event'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.venue'].omitValue).toBe(false);
});

test('it force hides fields with hidden visibility config', async () => {
    await Fields.setHiddenFieldsState([
        {handle: 'first_name'},
        {handle: 'last_name', visibility: 'hidden'},
    ]);

    expect(Store.state.publish.base.hiddenFields['first_name'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['last_name'].hidden).toBe('force');
    expect(Store.state.publish.base.hiddenFields['first_name'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['last_name'].omitValue).toBe(false);
});

test('it tells omitter to omit hidden fields by default', async () => {
    Fields.setValues({
        is_online_event: false,
        venue: false,
    });

    await Fields.setHiddenFieldsState([
        {handle: 'is_online_event'},
        {handle: 'venue', if: {is_online_event: true}},
    ]);

    expect(Store.state.publish.base.hiddenFields['is_online_event'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['is_online_event'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['venue'].omitValue).toBe(true);
});

test('it tells omitter to omit nested hidden fields by default', async () => {
    Fields.setValues({
        is_online_event: false,
        event_venue: false,
    }, 'nested');

    await Fields.setHiddenFieldsState([
        {handle: 'is_online_event'},
        {handle: 'venue', if: {is_online_event: true}},
    ], 'nested');

    expect(Store.state.publish.base.hiddenFields['nested.is_online_event'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.is_online_event'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.venue'].omitValue).toBe(true);
});

test('it tells omitter to omit revealer fields', async () => {
    Fields.setValues({
        revealer_toggle: false,
        regular_toggle: false,
    });

    await Fields.setHiddenFieldsState([
        {handle: 'revealer_toggle', type: 'revealer'},
        {handle: 'regular_toggle', type: 'regular'},
    ]);

    expect(Store.state.publish.base.hiddenFields['revealer_toggle'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['regular_toggle'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['revealer_toggle'].omitValue).toBe(true);
    expect(Store.state.publish.base.hiddenFields['regular_toggle'].omitValue).toBe(false);
});

test('it tells omitter to omit nested revealer fields', async () => {
    Fields.setValues({
        revealer_toggle: false,
        regular_toggle: false,
    }, 'nested');

    await Fields.setHiddenFieldsState([
        {handle: 'revealer_toggle', type: 'revealer'},
        {handle: 'regular_toggle', type: 'regular'},
    ], 'nested');

    expect(Store.state.publish.base.hiddenFields['nested.revealer_toggle'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.regular_toggle'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.revealer_toggle'].omitValue).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.regular_toggle'].omitValue).toBe(false);
});

test('it tells omitter not omit revealer-hidden fields', async () => {
    Fields.setValues({
        show_more_info: false,
        event_venue: false,
    });

    await Fields.setHiddenFieldsState([
        {handle: 'show_more_info', type: 'revealer'},
        {handle: 'venue', if: {show_more_info: true}},
    ]);

    expect(Store.state.publish.base.hiddenFields['show_more_info'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['show_more_info'].omitValue).toBe(true);
    expect(Store.state.publish.base.hiddenFields['venue'].omitValue).toBe(false);
});

test('it tells omitter not omit nested revealer-hidden fields', async () => {
    Fields.setValues({
        show_more_info: false,
        event_venue: false,
    }, 'nested');

    await Fields.setHiddenFieldsState([
        {handle: 'show_more_info', type: 'revealer'},
        {handle: 'venue', if: {show_more_info: true}},
    ], 'nested');

    expect(Store.state.publish.base.hiddenFields['nested.show_more_info'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.show_more_info'].omitValue).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.venue'].omitValue).toBe(false);
});

test('it tells omitter not omit prefixed revealer-hidden fields', async () => {
    Fields.setValues({
        prefixed_show_more_info: false,
        prefixed_event_venue: false,
    });

    await Fields.setHiddenFieldsState([
        {handle: 'prefixed_show_more_info', prefix: 'prefixed_', type: 'revealer'},
        {handle: 'prefixed_venue', prefix: 'prefixed_', if: {show_more_info: true}},
    ]);

    expect(Store.state.publish.base.hiddenFields['prefixed_show_more_info'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['prefixed_venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['prefixed_show_more_info'].omitValue).toBe(true);
    expect(Store.state.publish.base.hiddenFields['prefixed_venue'].omitValue).toBe(false);
});

test('it tells omitter not omit nested prefixed revealer-hidden fields', async () => {
    Fields.setValues({
        prefixed_show_more_info: false,
        prefixed_event_venue: false,
    }, 'nested');

    await Fields.setHiddenFieldsState([
        {handle: 'prefixed_show_more_info', prefix: 'prefixed_', type: 'revealer'},
        {handle: 'prefixed_venue', prefix: 'prefixed_', if: {show_more_info: true}},
    ], 'nested');

    expect(Store.state.publish.base.hiddenFields['nested.prefixed_show_more_info'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.prefixed_venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.prefixed_show_more_info'].omitValue).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.prefixed_venue'].omitValue).toBe(false);
});

test('it properly omits revealer-hidden fields when multiple conditions are set', async () => {
    Fields.setValues({
        show_more_info: false,
        has_second_event_venue: true,
        has_third_event_venue: false,
        event_venue_one: 'Stadium One',
        event_venue_two: 'Stadium Two',
        event_venue_three: false,
    });

    await Fields.setHiddenFieldsState([
        {handle: 'show_more_info', type: 'revealer'},
        {handle: 'has_second_event_venue', type: 'toggle', if: {show_more_info: true}},
        {handle: 'has_third_event_venue', type: 'toggle', if: {show_more_info: true}},
        {handle: 'event_venue_one', if: {show_more_info: true}},
        {handle: 'event_venue_two', if: {show_more_info: true, has_second_event_venue: true}},
        {handle: 'event_venue_three', if: {show_more_info: true, has_third_event_venue: true}},
    ]);

    expect(Store.state.publish.base.hiddenFields['show_more_info'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['has_second_event_venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['has_third_event_venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['event_venue_one'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['event_venue_two'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['event_venue_three'].hidden).toBe(true);

    expect(Store.state.publish.base.hiddenFields['show_more_info'].omitValue).toBe(true);
    expect(Store.state.publish.base.hiddenFields['has_second_event_venue'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['has_third_event_venue'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['event_venue_one'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['event_venue_two'].omitValue).toBe(false);

    // Though this third venue is hidden by a revealer, it's also disabled by a regular toggle condition, so it should actually be omitted...
    expect(Store.state.publish.base.hiddenFields['event_venue_three'].omitValue).toBe(true);
});

test('it properly omits nested revealer-hidden fields when multiple conditions are set', async () => {
    Fields.setValues({
        show_more_info: false,
        has_second_event_venue: true,
        has_third_event_venue: false,
        event_venue_one: 'Stadium One',
        event_venue_two: 'Stadium Two',
        event_venue_three: false,
    }, 'nested');

    await Fields.setHiddenFieldsState([
        {handle: 'show_more_info', type: 'revealer'},
        {handle: 'has_second_event_venue', type: 'toggle', if: {show_more_info: true}},
        {handle: 'has_third_event_venue', type: 'toggle', if: {show_more_info: true}},
        {handle: 'event_venue_one', if: {show_more_info: true}},
        {handle: 'event_venue_two', if: {show_more_info: true, has_second_event_venue: true}},
        {handle: 'event_venue_three', if: {show_more_info: true, has_third_event_venue: true}},
    ], 'nested');

    expect(Store.state.publish.base.hiddenFields['nested.show_more_info'].hidden).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.has_second_event_venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.has_third_event_venue'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.event_venue_one'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.event_venue_two'].hidden).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.event_venue_three'].hidden).toBe(true);

    expect(Store.state.publish.base.hiddenFields['nested.show_more_info'].omitValue).toBe(true);
    expect(Store.state.publish.base.hiddenFields['nested.has_second_event_venue'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.has_third_event_venue'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.event_venue_one'].omitValue).toBe(false);
    expect(Store.state.publish.base.hiddenFields['nested.event_venue_two'].omitValue).toBe(false);

    // Though this third venue is hidden by a revealer, it's also disabled by a regular toggle condition, so it should actually be omitted...
    expect(Store.state.publish.base.hiddenFields['nested.event_venue_three'].omitValue).toBe(true);
});
