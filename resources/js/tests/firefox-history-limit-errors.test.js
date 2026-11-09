import { afterEach, describe, expect, test, vi } from 'vitest';
import catchFirefoxHistoryLimitErrors from '../util/firefox-history-limit-errors';

const originalPushState = window.history.pushState;

function errorNamed(name) {
    return Object.assign(new Error(''), { name });
}

/** @see https://github.com/statamic/cms/issues/15599 */
describe('catchFirefoxHistoryLimitErrors', () => {
    afterEach(() => {
        window.history.pushState = originalPushState;
    });

    test('pushes state as normal', () => {
        const pushState = vi.fn();
        window.history.pushState = pushState;

        catchFirefoxHistoryLimitErrors();
        window.history.pushState({ page: 'data' }, '', '/cp/entries/1');

        expect(pushState).toHaveBeenCalledWith({ page: 'data' }, '', '/cp/entries/1');
    });

    test('throws a QuotaExceededError when Firefox rejects the state for being too large', () => {
        window.history.pushState = () => {
            throw errorNamed('NS_ERROR_ILLEGAL_VALUE');
        };

        catchFirefoxHistoryLimitErrors();

        expect(() => window.history.pushState({}, '', '/cp/entries/1')).toThrow(
            expect.objectContaining({ name: 'QuotaExceededError' }),
        );
    });

    test('rethrows other errors', () => {
        const error = errorNamed('SecurityError');

        window.history.pushState = () => {
            throw error;
        };

        catchFirefoxHistoryLimitErrors();

        expect(() => window.history.pushState({}, '', '/cp/entries/1')).toThrow(error);
    });
});
