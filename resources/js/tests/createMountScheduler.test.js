import { test, expect, vi, beforeEach, afterEach } from 'vitest';
import { createMountScheduler, flushMountSchedulers } from '../util/createMountScheduler.js';
import { mount } from '@vue/test-utils';
import { nextTick, ref, h } from 'vue';

beforeEach(() => {
    vi.useFakeTimers({
        toFake: [
            'setTimeout', 'clearTimeout',
            'setInterval', 'clearInterval',
            'setImmediate', 'clearImmediate',
            'queueMicrotask',
            'requestAnimationFrame', 'cancelAnimationFrame',
            'requestIdleCallback', 'cancelIdleCallback',
            'Date',
        ],
    });
});

afterEach(() => {
    vi.useRealTimers();
});

test('zero-arg factory still works (backward compat)', () => {
    const scheduler = createMountScheduler();
    expect(scheduler).toHaveProperty('schedule');
    expect(typeof scheduler.schedule).toBe('function');
});

// Helper to flush requestAnimationFrame / requestIdleCallback
const flushTick = async () => {
    await vi.advanceTimersToNextTimerAsync();
};

test('processes multiple cheap callbacks within budget', async () => {
    const scheduler = createMountScheduler({ budgetMs: 8 });
    const results = [];

    // Schedule 5 cheap callbacks
    for (let i = 0; i < 5; i++) {
        scheduler.schedule(() => results.push(i));
    }

    await flushTick();

    // All 5 should complete in one tick since they're cheap
    expect(results).toEqual([0, 1, 2, 3, 4]);
});

test('resumes remaining callbacks on next tick when budget exceeded', async () => {
    let processedCount = 0;
    const scheduler = createMountScheduler({ budgetMs: 5 });

    // Schedule callbacks - first one is slow, others are fast
    scheduler.schedule(() => {
        const start = performance.now();
        while (performance.now() - start < 10) {} // 10ms sync delay
        processedCount++;
    });

    for (let i = 0; i < 4; i++) {
        scheduler.schedule(() => processedCount++);
    }

    await flushTick();
    // Budget was blown by first callback, so only 1 processed
    expect(processedCount).toBe(1);

    await flushTick();
    // Next tick should process the rest
    expect(processedCount).toBe(5);
});

test('error in one callback does not prevent others from running', async () => {
    const scheduler = createMountScheduler({ budgetMs: 8 });
    const results = [];

    scheduler.schedule(() => results.push(1));
    scheduler.schedule(() => { throw new Error('Intentional error'); });
    scheduler.schedule(() => results.push(3));

    // Spy on console.error
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    await flushTick();

    expect(results).toEqual([1, 3]);
    expect(consoleSpy).toHaveBeenCalledOnce();

    consoleSpy.mockRestore();
});

test('many cheap callbacks finish within reasonable iterations', async () => {
    const scheduler = createMountScheduler({ budgetMs: 8 });
    const results = [];

    // 20 cheap callbacks
    for (let i = 0; i < 20; i++) {
        scheduler.schedule(() => results.push(i));
    }

    let iterations = 0;
    while (results.length < 20 && iterations < 10) {
        await flushTick();
        iterations++;
    }

    expect(results.length).toBe(20);
    expect(iterations).toBeLessThanOrEqual(3);
});

test('vue render time from a callback counts against the budget', async () => {
    // Component that takes measurable time to render when it mounts.
    const HeavyChild = {
        setup() {
            // Synthetic cost at setup time.
            const start = performance.now();
            while (performance.now() - start < 6) {}
            return () => h('div');
        },
    };
    const Parent = {
        setup() {
            const show = ref(false);
            return { show };
        },
        render() {
            return this.show ? h(HeavyChild) : h('div');
        },
    };

    const scheduler = createMountScheduler({ budgetMs: 5 });
    const wrappers = [mount(Parent), mount(Parent), mount(Parent)];
    const mounted = [];

    wrappers.forEach((w, i) => {
        scheduler.schedule(() => {
            w.vm.show = true;
            mounted.push(i);
        });
    });

    await flushTick();
    // First flip triggers HeavyChild mount (~6ms real time via nextTick);
    // budget is 5ms, so only one should process this tick.
    expect(mounted.length).toBe(1);

    await flushTick();
    await flushTick();
    expect(mounted.length).toBe(3);

    wrappers.forEach(w => w.unmount());
});

test('scheduler recovers after a nextTick rejection', async () => {
    const scheduler = createMountScheduler({ budgetMs: 8 });
    const results = [];

    // Mock nextTick to reject on the first invocation only.
    const realNextTick = nextTick;
    let first = true;
    const spy = vi.spyOn(await import('vue'), 'nextTick').mockImplementation((...args) => {
        if (first) { first = false; return Promise.reject(new Error('boom')); }
        return realNextTick(...args);
    });
    const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

    scheduler.schedule(() => results.push('a'));
    scheduler.schedule(() => results.push('b'));

    await flushTick();
    await flushTick();

    expect(results).toEqual(['a', 'b']);
    expect(consoleSpy).toHaveBeenCalled();

    spy.mockRestore();
    consoleSpy.mockRestore();
});

test('a callback that schedules more work runs on a later tick', async () => {
    const scheduler = createMountScheduler({ budgetMs: 8 });
    const order = [];

    scheduler.schedule(() => {
        order.push('a');
        scheduler.schedule(() => order.push('b'));
    });

    await flushTick();
    expect(order).toEqual(['a', 'b']);
});

test('respects budgetMs when requestIdleCallback fires with didTimeout', async () => {
    const originalRIC = globalThis.requestIdleCallback;
    let ricCallCount = 0;
    globalThis.requestIdleCallback = (cb) => {
        ricCallCount++;
        setTimeout(() => cb({ didTimeout: true, timeRemaining: () => 0 }), 0);
        return 0;
    };

    try {
        const scheduler = createMountScheduler({ budgetMs: 5 });
        const results = [];

        // First callback exceeds the 5ms budget.
        scheduler.schedule(() => {
            const start = performance.now();
            while (performance.now() - start < 10) {}
            results.push('heavy');
        });
        for (let i = 0; i < 4; i++) scheduler.schedule(() => results.push(i));

        // Drain until all five have run; cap iterations to avoid infinite loops on regression.
        for (let i = 0; i < 10 && results.length < 5; i++) {
            await flushTick();
        }

        expect(results).toEqual(['heavy', 0, 1, 2, 3]);

        // With the fix the busy callback forces a yield, so rIC is entered at
        // least twice. With the old `return false` bypass, all 5 drain in one
        // batch and ricCallCount stays at 1.
        expect(ricCallCount).toBeGreaterThan(1);
    } finally {
        if (originalRIC) globalThis.requestIdleCallback = originalRIC;
        else delete globalThis.requestIdleCallback;
    }
});

test('yields based on IdleDeadline.timeRemaining when idle is granted', async () => {
    const originalRIC = globalThis.requestIdleCallback;
    let ricCallCount = 0;
    globalThis.requestIdleCallback = (cb) => {
        ricCallCount++;
        const grantTime = performance.now();
        const deadline = {
            didTimeout: false,
            timeRemaining: () => Math.max(0, 10 - (performance.now() - grantTime)),
        };
        setTimeout(() => cb(deadline), 0);
        return 0;
    };

    try {
        const scheduler = createMountScheduler({ budgetMs: 100 });
        const results = [];

        for (let i = 0; i < 3; i++) {
            scheduler.schedule(() => {
                const start = performance.now();
                while (performance.now() - start < 6) {}
                results.push(i);
            });
        }

        for (let i = 0; i < 10 && results.length < 3; i++) {
            await flushTick();
        }

        expect(results).toEqual([0, 1, 2]);
        expect(ricCallCount).toBeGreaterThan(1);
    } finally {
        if (originalRIC) globalThis.requestIdleCallback = originalRIC;
        else delete globalThis.requestIdleCallback;
    }
});

test('scheduler resumes cleanly after an earlier flush completes', async () => {
    const scheduler = createMountScheduler({ budgetMs: 8 });
    const results = [];

    scheduler.schedule(() => results.push('a'));
    await flushTick();
    expect(results).toEqual(['a']);

    scheduler.schedule(() => results.push('b'));
    scheduler.schedule(() => results.push('c'));
    await flushTick();
    expect(results).toEqual(['a', 'b', 'c']);
});

// A save reads the condition bookkeeping that deferred set bodies do when they mount, so
// it has to be able to drain the queue on demand rather than waiting for idle. None of
// these tests advance a timer: draining must not depend on requestIdleCallback or
// requestAnimationFrame, neither of which a browser reliably fires in a background tab.

test('flushMountSchedulers drains queued callbacks without waiting for idle', async () => {
    const scheduler = createMountScheduler();
    const results = [];

    scheduler.schedule(() => results.push('a'));
    scheduler.schedule(() => results.push('b'));

    expect(results).toEqual([]);

    await flushMountSchedulers();

    expect(results).toEqual(['a', 'b']);
});

test('flushMountSchedulers drains work queued by the callbacks it runs', async () => {
    const outer = createMountScheduler();
    const inner = createMountScheduler();
    const results = [];

    // A set being mounted can prewarm sets nested inside it, and those use their own
    // scheduler.
    outer.schedule(() => {
        results.push('outer');
        inner.schedule(() => results.push('inner'));
    });

    await flushMountSchedulers();

    expect(results).toEqual(['outer', 'inner']);
});

test('flushMountSchedulers keeps going after a callback throws', async () => {
    const scheduler = createMountScheduler();
    const results = [];
    const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});

    scheduler.schedule(() => { throw new Error('nope'); });
    scheduler.schedule(() => results.push('b'));

    await flushMountSchedulers();

    expect(results).toEqual(['b']);
    consoleError.mockRestore();
});

test('flushMountSchedulers ignores a callback that never settles', async () => {
    const scheduler = createMountScheduler();
    const results = [];

    // Awaiting this would hang the save, and a locked form is worse than a stale field.
    scheduler.schedule(() => new Promise(() => {}));
    scheduler.schedule(() => results.push('b'));

    await flushMountSchedulers();

    expect(results).toEqual(['b']);
});

test('flushMountSchedulers gives up rather than hanging on a callback that keeps rescheduling', async () => {
    const scheduler = createMountScheduler();
    let runs = 0;

    const reschedule = () => {
        runs++;
        scheduler.schedule(reschedule);
    };
    scheduler.schedule(reschedule);

    const consoleWarn = vi.spyOn(console, 'warn').mockImplementation(() => {});

    await flushMountSchedulers({ timeoutMs: 20 });

    expect(runs).toBeGreaterThan(0);
    consoleWarn.mockRestore();
});

test('a settled queue is not retained, so a later flush is a no-op', async () => {
    const scheduler = createMountScheduler();
    const results = [];

    scheduler.schedule(() => results.push('a'));
    await flushMountSchedulers();
    await flushMountSchedulers();

    expect(results).toEqual(['a']);
});
