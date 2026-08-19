import { beforeEach, describe, expect, test, vi } from 'vitest';
import { ref } from 'vue';
import Hooks from '@/components/Hooks.js';
import { AfterSaveHooks, BeforeSaveHooks, Pipeline, PipelineStopped } from '@/components/ui/Publish/SavePipeline.js';

let saving;
let errors;
let container;

// If a step leaves its promise unsettled the pipeline hangs, so fail fast rather than waiting for the suite timeout.
function withTimeout(promise, ms = 1000) {
    return Promise.race([
        promise,
        new Promise((resolve, reject) => setTimeout(() => reject(new Error('Pipeline never settled')), ms)),
    ]);
}

function pipeline(steps) {
    return withTimeout(new Pipeline().provide({ container, errors, saving }).through(steps));
}

function step() {
    return { handle: vi.fn((payload) => payload) };
}

function throwingStep(error) {
    return {
        handle: () => {
            throw error;
        },
    };
}

beforeEach(() => {
    saving = ref(false);
    errors = ref({});
    container = ref({ saving: vi.fn(), saved: vi.fn() });
    global.Statamic = { $hooks: new Hooks() };
});

test('it runs the steps and finishes', async () => {
    const middle = step();

    await pipeline([new BeforeSaveHooks('entry', {}), middle, new AfterSaveHooks('entry', {})]);

    expect(middle.handle).toHaveBeenCalled();
    expect(saving.value).toBe(false);
    expect(container.value.saved).toHaveBeenCalled();
});

test('it passes the payload through the hooks', async () => {
    const saved = vi.fn();

    Statamic.$hooks.on('entry.saving', (resolve, reject, payload) => {
        expect(payload).toEqual({ collection: 'pages' });
        resolve();
    });

    Statamic.$hooks.on('entry.saved', (resolve, reject, payload) => {
        saved(payload);
        resolve();
    });

    await pipeline([
        new BeforeSaveHooks('entry', { collection: 'pages' }),
        { handle: () => 'the-response' },
        new AfterSaveHooks('entry', { collection: 'pages' }),
    ]);

    expect(saved).toHaveBeenCalledWith({ collection: 'pages', response: 'the-response' });
});

describe('a rejecting hook', () => {
    test('stops the pipeline when saving', async () => {
        const next = step();
        Statamic.$hooks.on('entry.saving', (resolve, reject) => reject(new Error('nope')));

        await expect(pipeline([new BeforeSaveHooks('entry', {}), next])).rejects.toThrow('nope');

        expect(next.handle).not.toHaveBeenCalled();
        expect(saving.value).toBe(false);
        expect(container.value.saved).not.toHaveBeenCalled();
    });

    test('stops the pipeline when saved', async () => {
        Statamic.$hooks.on('entry.saved', (resolve, reject) => reject(new Error('nope')));

        await expect(pipeline([new AfterSaveHooks('entry', {})])).rejects.toThrow('nope');

        expect(saving.value).toBe(false);
        expect(container.value.saved).not.toHaveBeenCalled();
    });
});

describe('a throwing hook', () => {
    test('stops the pipeline when saving', async () => {
        Statamic.$hooks.on('entry.saving', () => {
            throw new Error('nope');
        });

        await expect(pipeline([new BeforeSaveHooks('entry', {})])).rejects.toThrow('nope');

        expect(saving.value).toBe(false);
    });

    test('stops the pipeline when saved', async () => {
        Statamic.$hooks.on('entry.saved', () => {
            throw new Error('nope');
        });

        await expect(pipeline([new AfterSaveHooks('entry', {})])).rejects.toThrow('nope');

        expect(saving.value).toBe(false);
    });
});

test('a stopped pipeline resets the saving state', async () => {
    const next = step();

    await expect(pipeline([throwingStep(new PipelineStopped()), next])).rejects.toThrow(PipelineStopped);

    expect(next.handle).not.toHaveBeenCalled();
    expect(saving.value).toBe(false);
});

test('a step that throws resets the saving state', async () => {
    await expect(pipeline([throwingStep(new Error('nope'))])).rejects.toThrow('nope');

    expect(saving.value).toBe(false);
});
