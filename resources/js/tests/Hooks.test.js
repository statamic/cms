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

test('it runs without hooks', () => {
    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return promise.then(() => {
        expect(payload.count).toBe(1);
    });
});

test('it sets and runs hooks', () => {
    Statamic.$hooks.on('example.hook', (resolve, reject, data) => {
        expect(data.count).toBe(1);
        data.count = 2;
        resolve('first');
    });

    Statamic.$hooks.on('example.hook', (resolve, reject, data) => {
        expect(data.count).toBe(2);
        data.count = 3;
        resolve('second');
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return promise.then(results => {
        expect(results).toMatchObject(['first', 'second']);
        expect(payload.count).toBe(3);
    });
});

test('it sets and runs a failed hook', () => {
    Statamic.$hooks.on('example.hook', (resolve, reject, data) => {
        expect(data.count).toBe(1);
        resolve('resolved!');
    });

    Statamic.$hooks.on('example.hook', (resolve, reject) => {
        reject('rejected!');
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return expect(promise).rejects.toMatch('rejected!');
});

test('a rejected hook will stop other hooks running', () => {
    let runHooks = [];

    Statamic.$hooks.on('example.hook', (resolve, reject, data) => {
        runHooks.push('first');
        resolve('first');
    });

    Statamic.$hooks.on('example.hook', (resolve, reject, data) => {
        runHooks.push('second');
        reject('second');
    });

    Statamic.$hooks.on('example.hook', (resolve, reject, data) => {
        runHooks.push('third');
        resolve('third');
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return promise.catch(error => {
        expect(error).toBe('second');
        expect(runHooks).toMatchObject(['first', 'second']);
    });
});

test('it waits for hook promise to resolve', () => {
    Statamic.$hooks.on('example.hook', (resolve, reject, data) => {
        setTimeout(() => {
            data.count = 2;
            resolve();
        }, 10);
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return promise.then(() => {
        expect(payload.count).toBe(2);
    });
});

test('it runs hooks in order by priority', () => {
    // This hook defaults to priority of 10.
    Statamic.$hooks.on('example.hook', resolve => {
        resolve('second');
    });

    Statamic.$hooks.on('example.hook', resolve => {
        resolve('fifth');
    }, 2);

    Statamic.$hooks.on('example.hook', resolve => {
        resolve('third');
    });

    Statamic.$hooks.on('example.hook', resolve => {
        resolve('fourth');
    }, 5);

    Statamic.$hooks.on('example.hook', resolve => {
        resolve('first');
    }, 200);

    let promise = Statamic.$hooks.run('example.hook');

    return expect(promise).resolves.toMatchObject([
        'first', 'second', 'third', 'fourth', 'fifth'
    ]);
});
