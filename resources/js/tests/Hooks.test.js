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
    Statamic.$hooks.on('example.hook', (resolve, reject, data) => {
        expect(data.count).toBe(1);
        data.count = 2;
        resolve();
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return promise.then(() => {
        expect(payload.count).toBe(2);
    });
});

test('it sets and runs a failed hook', () => {
    Statamic.$hooks.on('example.hook', (resolve, reject, data) => {
        expect(data.count).toBe(1);
        resolve();
    });

    Statamic.$hooks.on('example.hook', (resolve, reject) => {
        reject();
    });

    let payload = {count: 1};
    let promise = Statamic.$hooks.run('example.hook', payload);

    return expect(promise).rejects.toMatch('');
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

test('it sets and runs a hook that passes a promise instead of a callback', () => {
    let runHooks = [];

    Statamic.$hooks.on('example.hook', new Promise(resolve => {
        setTimeout(() => {
            runHooks.push('we passed our own promise');
            resolve();
        }, 10);
    }));

    let promise = Statamic.$hooks.run('example.hook');

    return promise.then(() => {
        expect(runHooks[0]).toBe('we passed our own promise');
    });
});

test('it runs hooks in order by priority', () => {
    let runHooks = [];

    // This hook defaults to priority of 10.
    Statamic.$hooks.on('example.hook', resolve => {
        runHooks.push('this should run second');
        resolve();
    });

    Statamic.$hooks.on('example.hook', resolve => {
        runHooks.push('this should run third');
        resolve();
    }, 200);

    Statamic.$hooks.on('example.hook', resolve => {
        runHooks.push('this should run first');
        resolve();
    }, 2);

    let promise = Statamic.$hooks.run('example.hook');

    return promise.then(() => {
        expect(runHooks[0]).toBe('this should run first');
        expect(runHooks[1]).toBe('this should run second');
        expect(runHooks[2]).toBe('this should run third');
    });
});

test('it can run before and after hooks in one shot', () => {
    let runHooks = [];

    Statamic.$hooks.on('example.save.after', resolve => {
        runHooks.push('this should run after');
        resolve();
    });

    Statamic.$hooks.on('example.save.before', resolve => {
        runHooks.push('this should run before');
        resolve();
    });

    let saveOperation = resolve => {
        setTimeout(() => {
            runHooks.push('this should run in the middle');
            resolve();
        }, 10);
    };

    return Statamic.$hooks.runBeforeAndAfter(saveOperation, 'example.save').then(() => {
        expect(runHooks[0]).toBe('this should run before');
        expect(runHooks[1]).toBe('this should run in the middle');
        expect(runHooks[2]).toBe('this should run after');
    });
});

test('it can run before and after hooks in one shot, but rejecting any befores stops the action and afters', () => {
    let runHooks = [];

    Statamic.$hooks.on('example.save.after', resolve => {
        runHooks.push('this should not run');
        resolve();
    });

    Statamic.$hooks.on('example.save.before', (resolve, reject) => {
        runHooks.push('this should run before');
        reject();
    });

    let saveOperation = resolve => {
        setTimeout(() => {
            runHooks.push('this should not run');
            resolve();
        }, 10);
    };

    return Statamic.$hooks.runBeforeAndAfter(saveOperation, 'example.save').catch(() => {
        expect(runHooks.length).toBe(1);
        expect(runHooks[0]).toBe('this should run before');
    });
});
test('it can run before and after hooks and gets success from passed promise', () => {
    let saveOperation = resolve => {
        setTimeout(() => {
            resolve('twas a success, mate');
        }, 10);
    };

    return Statamic.$hooks.runBeforeAndAfter(saveOperation, 'example.save').then(success => {
        expect(success).toBe('twas a success, mate');
    });
});

test('it can run before and after hooks and gets error from passed promise', () => {
    let saveOperation = (resolve, reject) => {
        setTimeout(() => {
            reject('houston, we have a problem');
        }, 10);
    };

    return Statamic.$hooks.runBeforeAndAfter(saveOperation, 'example.save').catch(error => {
        expect(error).toBe('houston, we have a problem');
    });
});
