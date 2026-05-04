import { test, expect, beforeEach, vi } from 'vitest';

// Mock @inertiajs/vue3 router before importing the composable so it captures
// the mock instead of the real router.
vi.mock('@inertiajs/vue3', () => {
    const listeners = { before: [], success: [] };
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

const setupGlobals = () => {
    global.Statamic = {
        $preferences: {
            get: () => true,
        },
    };
    global.__ = (key) => key;
};

let useDirtyState;

beforeEach(async () => {
    vi.resetModules();
    setupGlobals();
    window.history.replaceState({ page: 'A', url: '/a' }, '', '/a');
    useDirtyState = (await import('../composables/dirty-state.js')).default;
});

test('popstate is ignored when nothing is dirty', () => {
    const { count } = useDirtyState();
    const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true);

    window.dispatchEvent(new PopStateEvent('popstate', { state: null }));

    expect(confirmSpy).not.toHaveBeenCalled();
    expect(count()).toBe(0);

    confirmSpy.mockRestore();
});

test('popstate prompts the user when the form is dirty', () => {
    const { add, count } = useDirtyState();
    const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true);
    const backSpy = vi.spyOn(window.history, 'back').mockImplementation(() => {});

    add('entry');
    expect(count()).toBe(1);

    window.dispatchEvent(new PopStateEvent('popstate', { state: { page: 'A' } }));

    expect(confirmSpy).toHaveBeenCalledWith('statamic::messages.dirty_navigation_warning');
    expect(count()).toBe(0); // dirty cleared on confirmation
    expect(backSpy).toHaveBeenCalled();

    confirmSpy.mockRestore();
    backSpy.mockRestore();
});

test('cancelling the prompt re-pushes the dirty page state and keeps form dirty', () => {
    const { add, count } = useDirtyState();

    // The dirty URL/state is captured at the moment add() is called.
    window.history.replaceState({ page: 'B', url: '/b' }, '', '/b');
    add('entry');

    const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(false);
    const pushSpy = vi.spyOn(window.history, 'pushState');
    const backSpy = vi.spyOn(window.history, 'back').mockImplementation(() => {});

    window.dispatchEvent(new PopStateEvent('popstate', { state: { page: 'A' } }));

    expect(confirmSpy).toHaveBeenCalled();
    expect(count()).toBe(1); // still dirty
    expect(backSpy).not.toHaveBeenCalled();
    expect(pushSpy).toHaveBeenCalledWith({ page: 'B', url: '/b' }, '', expect.stringContaining('/b'));

    confirmSpy.mockRestore();
    pushSpy.mockRestore();
    backSpy.mockRestore();
});

test('popstate stops propagation so Inertia\'s listener cannot wipe form data', () => {
    const { add } = useDirtyState();
    const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(false);

    add('entry');

    let inertiaListenerFired = false;
    const inertiaListener = () => { inertiaListenerFired = true; };
    window.addEventListener('popstate', inertiaListener);

    window.dispatchEvent(new PopStateEvent('popstate', { state: { page: 'A' } }));

    expect(inertiaListenerFired).toBe(false);

    window.removeEventListener('popstate', inertiaListener);
    confirmSpy.mockRestore();
});

test('popstate is ignored when confirm_dirty_navigation preference is disabled', () => {
    global.Statamic.$preferences.get = () => false;

    const { add } = useDirtyState();
    const confirmSpy = vi.spyOn(window, 'confirm').mockReturnValue(true);

    add('entry');

    window.dispatchEvent(new PopStateEvent('popstate', { state: { page: 'A' } }));

    expect(confirmSpy).not.toHaveBeenCalled();
    confirmSpy.mockRestore();
});
