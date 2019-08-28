import Vue from 'vue';
import Hooks from '../components/Hooks.js';
const hooks = new Hooks;

const Statamic = new Vue({
    computed: {
        $hooks() {
            return hooks;
        }
    }
});

afterEach(() => {
    Statamic.$hooks.hooks = {}
});

test('it sets and runs a hook', () => {
    Statamic.$hooks.on('entries.publish.before', data => {
        expect(data.count).toBe(1);
        data.count = 2;
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('entries.publish.before', payload);

    return promise.then(() => {
        expect(payload.count).toBe(2);
    });
});

test('it sets and runs a failed hook', () => {
    Statamic.$hooks.on('entries.publish.before', data => {
        expect(data.count).toBe(1);
    });

    Statamic.$hooks.on('entries.publish.before', data => {
        return false;
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('entries.publish.before', payload);

    return expect(promise).rejects.toMatch('');
});

test('it waits for hook defined promises to resolve', () => {
    Statamic.$hooks.on('entries.publish.before', data => {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                data.count = 2;
                resolve();
            }, 10);
        });
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('entries.publish.before', payload);

    return promise.then(() => {
        expect(payload.count).toBe(2);
    });
});

test('it fails if a hook defined promise is rejected', () => {
    Statamic.$hooks.on('entries.publish.before', data => {
        expect(data.count).toBe(1);
    });

    Statamic.$hooks.on('entries.publish.before', data => {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                reject();
            }, 10);
        });
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('entries.publish.before', payload);

    return expect(promise).rejects.toMatch('');
});

