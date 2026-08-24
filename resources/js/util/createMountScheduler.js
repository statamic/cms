import { nextTick } from 'vue';

const DEFAULT_BUDGET_MS = 8;

// Caps, not tuning knobs. Nothing should come close to them — they exist so the loops
// below are provably finite even if a scheduled callback misbehaves.
const MAX_CALLBACKS_PER_DRAIN = 5000;
const MAX_FLUSH_ROUNDS = 50;

// Backstop for the same reason. A save that leaves a hidden field in the payload is a
// bug; a save that never resolves locks the form with no recovery but a reload. If
// draining somehow can't finish, saving anyway is the better failure.
const FLUSH_TIMEOUT_MS = 2000;

// Every scheduler with something still queued. Schedulers are added on schedule() and
// removed once their queue empties, so nothing is retained between bursts.
const pending = new Set();

// Drain every queued mount immediately, so that a save reads condition bookkeeping that
// has actually happened. Deliberately does not go through flush(): it never waits for
// idle, only for microtasks, so a throttled background tab — where requestIdleCallback
// and requestAnimationFrame may not fire at all — can't stall it.
export function flushMountSchedulers({ timeoutMs = FLUSH_TIMEOUT_MS } = {}) {
    let timer;

    const timedOut = new Promise((resolve) => {
        timer = setTimeout(() => {
            console.warn('Timed out draining deferred field mounting; saving anyway.');
            resolve();
        }, timeoutMs);
    });

    return Promise.race([
        drainPending(performance.now() + timeoutMs).catch((e) => console.error(e)),
        timedOut,
    ]).finally(() => clearTimeout(timer));
}

async function drainPending(deadline) {
    let rounds = 0;

    // Mounting a set can queue more work — a nested replicator prewarming its own sets
    // uses its own scheduler — so keep going until nothing is left.
    while (pending.size && rounds++ < MAX_FLUSH_ROUNDS && performance.now() < deadline) {
        await Promise.all([...pending].map((drain) => drain(deadline)));
    }

    // ShowField commits its bookkeeping in a nextTick, and that commit can trigger
    // another round of evaluation, so let it reach a fixed point.
    for (let i = 0; i < 3; i++) await nextTick();
}

export function createMountScheduler({ budgetMs = DEFAULT_BUDGET_MS } = {}) {
    const queue = [];
    let flushing = false;

    const waitForIdle = () => new Promise((resolve) => {
        if (typeof requestIdleCallback === 'function') {
            requestIdleCallback(resolve, { timeout: 50 });
        } else {
            requestAnimationFrame(() => resolve());
        }
    });

    function schedule(callback) {
        queue.push(callback);
        pending.add(drain);
        if (!flushing) flush();
    }

    // Callbacks are called, never awaited: one that returned a promise it never settled
    // would otherwise stall a save. Between the callback cap here and the round cap in
    // drainPending(), this loop is finite — which is what lets the flush timeout fire at
    // all, since an unbounded microtask loop would starve it.
    async function drain(deadline = Infinity) {
        let ran = 0;

        while (queue.length && ran++ < MAX_CALLBACKS_PER_DRAIN && performance.now() < deadline) {
            const cb = queue.shift();
            try { cb?.(); } catch (e) { console.error(e); }
            try { await nextTick(); } catch (e) { console.error(e); }
        }

        if (!queue.length) pending.delete(drain);
    }

    async function flush() {
        flushing = true;
        try {
            while (queue.length) {
                let deadline;
                deadline = await waitForIdle();

                const frameStart = performance.now();
                const shouldYield = () => {
                    if (deadline && typeof deadline.timeRemaining === 'function' && !deadline.didTimeout) {
                        return deadline.timeRemaining() < 1;
                    }
                    return performance.now() - frameStart >= budgetMs;
                };

                while (queue.length && !shouldYield()) {
                    const cb = queue.shift();
                    try { cb?.(); } catch (e) { console.error(e); }
                    try { await nextTick(); } catch (e) { console.error(e); }
                }
            }
        } finally {
            flushing = false;
            if (!queue.length) pending.delete(drain);
        }
    }

    return { schedule };
}
