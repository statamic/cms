import { describe, test, expect, vi, beforeEach, afterEach } from 'vitest';
import Passkeys from '../../frontend/components/Passkeys.js';

vi.mock('@simplewebauthn/browser', () => ({
    browserSupportsWebAuthn: vi.fn(() => true),
    startAuthentication: vi.fn(),
    startRegistration: vi.fn(),
    WebAuthnAbortService: {
        cancelCeremony: vi.fn(),
    },
}));

import { browserSupportsWebAuthn, startAuthentication, startRegistration, WebAuthnAbortService } from '@simplewebauthn/browser';

describe('Passkeys', () => {
    let passkeys;

    beforeEach(() => {
        passkeys = new Passkeys();
        vi.clearAllMocks();

        global.fetch = vi.fn();

        Object.defineProperty(document, 'querySelector', {
            value: vi.fn(() => null),
            writable: true,
        });
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    test('it checks browser support', () => {
        expect(passkeys.supported).toBe(true);
    });

    test('it starts in non-waiting state', () => {
        expect(passkeys.waiting).toBe(false);
    });

    test('it starts with no error', () => {
        expect(passkeys.error).toBe(null);
    });

    describe('authenticate', () => {
        test('it fetches options and calls startAuthentication', async () => {
            const mockOptions = { challenge: 'test-challenge' };

            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve(mockOptions),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ redirect: '/dashboard' }),
                });

            startAuthentication.mockResolvedValueOnce({ id: 'credential-id' });

            const onSuccess = vi.fn();

            await passkeys.authenticate({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
                onSuccess,
            });

            expect(global.fetch).toHaveBeenCalledTimes(2);
            expect(startAuthentication).toHaveBeenCalledWith({
                optionsJSON: mockOptions,
                useBrowserAutofill: false,
            });
            expect(onSuccess).toHaveBeenCalledWith({ redirect: '/dashboard' });
        });

        test('it handles authentication errors gracefully', async () => {
            global.fetch.mockResolvedValueOnce({
                ok: true,
                json: () => Promise.resolve({ challenge: 'test' }),
            });

            const abortError = new Error('User cancelled');
            abortError.name = 'NotAllowedError';
            startAuthentication.mockRejectedValueOnce(abortError);

            const onError = vi.fn();

            await passkeys.authenticate({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
                onError,
            });

            // NotAllowedError should be silently ignored
            expect(onError).not.toHaveBeenCalled();
        });

        test('it handles verification failure', async () => {
            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ challenge: 'test' }),
                })
                .mockResolvedValueOnce({
                    ok: false,
                    status: 401,
                    json: () => Promise.resolve({ message: 'Invalid passkey' }),
                });

            startAuthentication.mockResolvedValueOnce({ id: 'credential-id' });

            const onError = vi.fn();

            await passkeys.authenticate({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
                onError,
            });

            expect(onError).toHaveBeenCalledWith({
                message: 'Invalid passkey',
                status: 401,
            });
            expect(passkeys.error).toBe('Invalid passkey');
        });
    });

    describe('register', () => {
        test('it fetches options and calls startRegistration', async () => {
            const mockOptions = { challenge: 'test-challenge' };

            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve(mockOptions),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ verified: true }),
                });

            startRegistration.mockResolvedValueOnce({ id: 'new-credential' });

            const onSuccess = vi.fn();

            await passkeys.register({
                optionsUrl: '/passkeys/create',
                verifyUrl: '/passkeys/store',
                name: 'My Passkey',
                onSuccess,
            });

            expect(global.fetch).toHaveBeenCalledTimes(2);
            expect(startRegistration).toHaveBeenCalledWith({
                optionsJSON: mockOptions,
            });
            expect(onSuccess).toHaveBeenCalledWith({ verified: true });
        });

        test('it uses default passkey name', async () => {
            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ challenge: 'test' }),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ verified: true }),
                });

            startRegistration.mockResolvedValueOnce({ id: 'new-credential' });

            await passkeys.register({
                optionsUrl: '/passkeys/create',
                verifyUrl: '/passkeys/store',
            });

            const lastCall = global.fetch.mock.calls[1];
            const body = JSON.parse(lastCall[1].body);
            expect(body.name).toBe('Passkey');
        });
    });

    describe('cancel', () => {
        test('it calls WebAuthnAbortService.cancelCeremony', () => {
            passkeys.cancel();
            expect(WebAuthnAbortService.cancelCeremony).toHaveBeenCalled();
        });
    });

    describe('initAutofill', () => {
        test('it calls authenticate with useBrowserAutofill', async () => {
            // Create a fresh instance to ensure supported is true
            browserSupportsWebAuthn.mockReturnValue(true);
            const freshPasskeys = new Passkeys();

            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ challenge: 'test' }),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ redirect: '/' }),
                });

            startAuthentication.mockResolvedValueOnce({ id: 'credential-id' });

            // Note: initAutofill returns immediately if not supported, so we need to await the authenticate call
            await freshPasskeys.initAutofill({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
            });

            // Small delay to allow async operation to complete
            await new Promise(resolve => setTimeout(resolve, 10));

            expect(startAuthentication).toHaveBeenCalledWith({
                optionsJSON: { challenge: 'test' },
                useBrowserAutofill: true,
            });
        });

        test('it does nothing if browser does not support webauthn', async () => {
            browserSupportsWebAuthn.mockReturnValueOnce(false);
            const noSupportPasskeys = new Passkeys();

            await noSupportPasskeys.initAutofill({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
            });

            expect(global.fetch).not.toHaveBeenCalled();
        });
    });

    describe('configure', () => {
        test('it returns the instance', () => {
            const result = passkeys.configure({ optionsUrl: '/options' });
            expect(result).toBe(passkeys);
        });

        test('it uses configured defaults for authenticate', async () => {
            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ challenge: 'test' }),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ redirect: '/dashboard' }),
                });

            startAuthentication.mockResolvedValueOnce({ id: 'credential-id' });

            const onSuccess = vi.fn();

            passkeys.configure({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
                onSuccess,
            });

            await passkeys.authenticate();

            expect(global.fetch).toHaveBeenCalledTimes(2);
            expect(onSuccess).toHaveBeenCalledWith({ redirect: '/dashboard' });
        });

        test('it uses configured defaults for register', async () => {
            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ challenge: 'test' }),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ verified: true }),
                });

            startRegistration.mockResolvedValueOnce({ id: 'new-credential' });

            const onSuccess = vi.fn();

            passkeys.configure({
                optionsUrl: '/passkeys/create',
                verifyUrl: '/passkeys/store',
                onSuccess,
            });

            await passkeys.register({ name: 'My Key' });

            expect(onSuccess).toHaveBeenCalledWith({ verified: true });

            const lastCall = global.fetch.mock.calls[1];
            const body = JSON.parse(lastCall[1].body);
            expect(body.name).toBe('My Key');
        });

        test('call-time options override configured defaults', async () => {
            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ challenge: 'test' }),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ redirect: '/' }),
                });

            startAuthentication.mockResolvedValueOnce({ id: 'credential-id' });

            const defaultSuccess = vi.fn();
            const overrideSuccess = vi.fn();

            passkeys.configure({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
                onSuccess: defaultSuccess,
            });

            await passkeys.authenticate({ onSuccess: overrideSuccess });

            expect(defaultSuccess).not.toHaveBeenCalled();
            expect(overrideSuccess).toHaveBeenCalledWith({ redirect: '/' });
        });

        test('it uses configured defaults for initAutofill', async () => {
            browserSupportsWebAuthn.mockReturnValue(true);
            const freshPasskeys = new Passkeys();

            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ challenge: 'test' }),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ redirect: '/' }),
                });

            startAuthentication.mockResolvedValueOnce({ id: 'credential-id' });

            const onSuccess = vi.fn();

            freshPasskeys.configure({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
                onSuccess,
            });

            await freshPasskeys.initAutofill();

            await new Promise(resolve => setTimeout(resolve, 10));

            expect(startAuthentication).toHaveBeenCalledWith({
                optionsJSON: { challenge: 'test' },
                useBrowserAutofill: true,
            });
        });
    });

    describe('CSRF token', () => {
        test('it reads CSRF token from meta tag', async () => {
            document.querySelector = vi.fn((selector) => {
                if (selector === 'meta[name="csrf-token"]') {
                    return { getAttribute: () => 'meta-csrf-token' };
                }
                return null;
            });

            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ challenge: 'test' }),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ redirect: '/' }),
                });

            startAuthentication.mockResolvedValueOnce({ id: 'credential-id' });

            await passkeys.authenticate({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
            });

            const lastCall = global.fetch.mock.calls[1];
            expect(lastCall[1].headers['X-CSRF-TOKEN']).toBe('meta-csrf-token');
        });

        test('it allows overriding CSRF token', async () => {
            global.fetch
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ challenge: 'test' }),
                })
                .mockResolvedValueOnce({
                    ok: true,
                    json: () => Promise.resolve({ redirect: '/' }),
                });

            startAuthentication.mockResolvedValueOnce({ id: 'credential-id' });

            await passkeys.authenticate({
                optionsUrl: '/passkeys/options',
                verifyUrl: '/passkeys/login',
                csrfToken: 'custom-token',
            });

            const lastCall = global.fetch.mock.calls[1];
            expect(lastCall[1].headers['X-CSRF-TOKEN']).toBe('custom-token');
        });
    });
});
