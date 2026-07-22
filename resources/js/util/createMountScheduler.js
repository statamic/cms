import { nextTick } from 'vue';

const DEFAULT_BUDGET_MS = 8;

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
        if (!flushing) flush();
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
        }
    }

    return { schedule };
}
