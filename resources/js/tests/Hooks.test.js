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

test('it sets and runs a hook', () => {
    Statamic.$hooks.on('example.hook', data => {
        expect(data.count).toBe(1);
        data.count = 2;
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return promise.then(() => {
        expect(payload.count).toBe(2);
    });
});

test('it sets and runs a failed hook', () => {
    Statamic.$hooks.on('example.hook', data => {
        expect(data.count).toBe(1);
    });

    Statamic.$hooks.on('example.hook', data => {
        return false;
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return expect(promise).rejects.toMatch('');
});

test('it waits for hook defined promises to resolve', () => {
    Statamic.$hooks.on('example.hook', data => {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                data.count = 2;
                resolve();
            }, 10);
        });
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return promise.then(() => {
        expect(payload.count).toBe(2);
    });
});

test('it fails if a hook defined promise is rejected', () => {
    Statamic.$hooks.on('example.hook', data => {
        expect(data.count).toBe(1);
    });

    Statamic.$hooks.on('example.hook', data => {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                reject();
            }, 10);
        });
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return expect(promise).rejects.toMatch('');
});

test('it runs hooks in order by priority', () => {
    let runHooks = [];

    // This hook defaults to priority of 10.
    Statamic.$hooks.on('example.hook', data => {
        runHooks.push('this should run second');
    });

    Statamic.$hooks.on('example.hook', data => {
        runHooks.push('this should run third');
    }, 200);

    Statamic.$hooks.on('example.hook', data => {
        runHooks.push('this should run first');
    }, 2);

    let promise = Statamic.$hooks.run('example.hook');

    return promise.then(() => {
        expect(runHooks[0]).toBe('this should run first');
        expect(runHooks[1]).toBe('this should run second');
        expect(runHooks[2]).toBe('this should run third');
    });
});
