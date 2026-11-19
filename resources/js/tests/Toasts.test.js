import { test, expect, beforeEach, vi } from 'vitest';

// Mock @inertiajs/vue3 router before importing the component so it captures
// the mock instead of the real router.
vi.mock('@inertiajs/vue3', () => {
    const listeners = { success: [] };
    return {
        router: {
            on: (event, callback) => {
                listeners[event].push(callback);
                return () => {
                    listeners[event] = listeners[event].filter((cb) => cb !== callback);
                };
            },
            __listeners: listeners,
        },
    };
});

const fakeAxios = { interceptors: { response: { use: () => {} } } };

let toasts;
let fireSuccess;

beforeEach(async () => {
    vi.resetModules();
    const { router } = await import('@inertiajs/vue3');
    router.__listeners.success = [];
    const { default: Toasts } = await import('../components/Toasts.js');
    toasts = new Toasts();
    toasts.success = vi.fn();
    toasts.error = vi.fn();
    toasts.registerInterceptor(fakeAxios);
    fireSuccess = (page) => router.__listeners.success.forEach((cb) => cb({ detail: { page } }));
});

test('toasts in the page props are displayed', () => {
    fireSuccess({ props: { _toasts: [{ type: 'success', message: 'Saved' }] } });

    expect(toasts.success).toHaveBeenCalledTimes(1);
    expect(toasts.success).toHaveBeenCalledWith('Saved', { duration: undefined });
});

test('nothing happens without toasts in the page props', () => {
    fireSuccess({ props: {} });
    fireSuccess({ props: { _toasts: [] } });

    expect(toasts.success).not.toHaveBeenCalled();
});

test('toasts are not replayed by partial reloads', () => {
    const page = { props: { _toasts: [{ type: 'success', message: 'Saved' }] } };

    fireSuccess(page);

    // Simulate a partial reload of the same component: Inertia merges the partial
    // response - which carries no _toasts key - into the existing page props.
    page.props = { ...page.props, time: '12:00:00' };
    fireSuccess(page);

    expect(toasts.success).toHaveBeenCalledTimes(1);
});
