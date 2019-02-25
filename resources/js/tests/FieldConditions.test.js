import Vue from 'vue';
import Vuex from 'vuex';
import FieldConditions from '../components/publish/FieldConditions.js';
require('../bootstrap/globals');
global._ = require('underscore');
Vue.use(Vuex);

const Store = new Vuex.Store({
    state: {publish: {base: {values: {}}}},
    mutations: {
        setValues(state, values) {
            this.state.publish.base.values = values;
        }
    }
});

const Fields = new Vue({
    mixins: [FieldConditions],
    store: Store
});

let showFieldIf = function (conditions=null) {
    return Fields.showField(conditions ? {'if': conditions} : {});
};

afterEach(() => {
    Store.commit('setValues', {});
});

test('it shows field by default', () => {
    expect(showFieldIf()).toBe(true);
});

test('it shows or hides field based on shorthand equals conditions', () => {
    Store.commit('setValues', {first_name: 'Jesse'});

    expect(showFieldIf({first_name: 'Jesse'})).toBe(true);
    expect(showFieldIf({first_name: 'Jack'})).toBe(false);
});

test('it can use custom operators in conditions', () => {
    Store.commit('setValues', {age: 13});

    expect(showFieldIf({age: '== 13'})).toBe(true);
    expect(showFieldIf({age: '!= 5'})).toBe(true);
    expect(showFieldIf({age: '=== 13'})).toBe(false); // Fails because we don't cast their condition to int
    expect(showFieldIf({age: '!== 13'})).toBe(true);

    expect(showFieldIf({age: '> 5'})).toBe(true);
    expect(showFieldIf({age: '> 13'})).toBe(false);
    expect(showFieldIf({age: '> 20'})).toBe(false);
    expect(showFieldIf({age: '>= 13'})).toBe(true);

    expect(showFieldIf({age: '< 5'})).toBe(false);
    expect(showFieldIf({age: '< 13'})).toBe(false);
    expect(showFieldIf({age: '< 20'})).toBe(true);
    expect(showFieldIf({age: '<= 13'})).toBe(true);

    expect(showFieldIf({age: 'is 13'})).toBe(true);
    expect(showFieldIf({age: 'equals 13'})).toBe(true);
    expect(showFieldIf({age: 'not 13'})).toBe(false);
});

test('it handles null and empty in condition as literal null', () => {
    Store.commit('setValues', {last_name: 'HasselHoff'});

    expect(showFieldIf({first_name: 'null'})).toBe(true);
    expect(showFieldIf({first_name: 'empty'})).toBe(true);
    expect(showFieldIf({last_name: 'not null'})).toBe(true);
    expect(showFieldIf({last_name: 'not empty'})).toBe(true);
});

test('it can use operators with multi-word values', () => {
    Store.commit('setValues', {ace_ventura_says: 'Allllllrighty then!'});

    expect(showFieldIf({ace_ventura_says: 'Allllllrighty then!'})).toBe(true);
    expect(showFieldIf({ace_ventura_says: '== Allllllrighty then!'})).toBe(true);
    expect(showFieldIf({ace_ventura_says: 'is Allllllrighty then!'})).toBe(true);
    expect(showFieldIf({ace_ventura_says: 'not I am your father'})).toBe(true);
});

test('it only shows when multiple conditions are met', () => {
    Store.commit('setValues', {
        first_name: 'San',
        last_name: 'Holo',
        age: 22
    });

    expect(showFieldIf({first_name: 'is San', last_name: 'is Holo', age: '!= 20'})).toBe(true);
    expect(showFieldIf({first_name: 'is San', last_name: 'is Holo', age: '> 40'})).toBe(false);
});

test('it can run conditions on nested data', () => {
    Store.commit('setValues', {
        related_posts: [
            {title: 'Learning Laravel', slug: 'learning-laravel'},
            {title: 'Learning Vue', slug: 'learning-vue'},
        ]
    });

    expect(showFieldIf({'related_posts.*.title': 'Learning Vue'})).toBe(true);
    expect(showFieldIf({'related_posts.*.title': 'Learning Vim'})).toBe(false);
})

