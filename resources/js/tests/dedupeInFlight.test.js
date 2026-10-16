import { describe, test, expect, vi } from 'vitest';
import { dedupeInFlight } from '../util/dedupeInFlight';

function deferred() {
    let resolve, reject;
    const promise = new Promise((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
}

describe('dedupeInFlight', () => {
    test('calls the factory once for concurrent callers sharing a key', async () => {
        const d = deferred();
        const factory = vi.fn(() => d.promise);

        const a = dedupeInFlight('ns', 'key', factory);
        const b = dedupeInFlight('ns', 'key', factory);

        expect(factory).toHaveBeenCalledTimes(1);

        d.resolve('value');

        expect(await a).toBe('value');
        expect(await b).toBe('value');
    });

    test('calls the factory immediately rather than deferring it', () => {
        const factory = vi.fn(() => Promise.resolve());

        dedupeInFlight('ns', 'sync', factory);

        expect(factory).toHaveBeenCalledTimes(1);
    });

    test('does not share between different keys or namespaces', async () => {
        const factory = vi.fn(() => Promise.resolve());

        dedupeInFlight('ns', 'one', factory);
        dedupeInFlight('ns', 'two', factory);
        dedupeInFlight('other', 'one', factory);

        expect(factory).toHaveBeenCalledTimes(3);
    });

    test('releases the entry once settled so a later call fetches fresh', async () => {
        const factory = vi.fn(() => Promise.resolve('value'));

        await dedupeInFlight('ns', 'settled', factory);
        await dedupeInFlight('ns', 'settled', factory);

        expect(factory).toHaveBeenCalledTimes(2);
    });

    test('rejects every caller when the shared work fails, and releases the entry', async () => {
        const d = deferred();
        const failing = vi.fn(() => d.promise);

        const a = dedupeInFlight('ns', 'failure', failing);
        const b = dedupeInFlight('ns', 'failure', failing);

        d.reject(new Error('nope'));

        await expect(a).rejects.toThrow('nope');
        await expect(b).rejects.toThrow('nope');

        await dedupeInFlight('ns', 'failure', () => Promise.resolve('ok'));
        expect(failing).toHaveBeenCalledTimes(1);
    });
});
