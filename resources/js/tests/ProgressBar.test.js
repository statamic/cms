import { test, expect, beforeEach } from 'vitest';
import { effect, nextTick } from 'vue';
import useProgressBar from '@/composables/progress-bar.js';

const progress = useProgressBar();

beforeEach(() => {
    // Reset any leftover operations between tests.
    progress.names().slice().forEach((name) => progress.complete(name));
});

test('loading is reported while operations are in flight', () => {
    expect(progress.isComplete()).toBe(true);

    progress.loading('a', true);
    expect(progress.isComplete()).toBe(false);
    expect(progress.count()).toBe(1);

    progress.loading('a', false);
    expect(progress.isComplete()).toBe(true);
    expect(progress.count()).toBe(0);
});

test('reactive consumers are only notified on start and stop transitions', async () => {
    let runs = 0;
    let lastComplete;

    effect(() => {
        lastComplete = progress.isComplete();
        runs++;
    });

    expect(runs).toBe(1); // initial run
    expect(lastComplete).toBe(true);

    // Add a large batch of operations synchronously.
    for (let i = 0; i < 200; i++) {
        progress.loading(`op-${i}`, true);
    }
    await nextTick();

    // Only one additional run for the idle -> loading transition.
    expect(runs).toBe(2);
    expect(lastComplete).toBe(false);

    // Remove all but the last operation. Still loading, so no new notification.
    for (let i = 0; i < 199; i++) {
        progress.loading(`op-${i}`, false);
    }
    await nextTick();
    expect(runs).toBe(2);

    // Remove the final operation. Now one run for the loading -> idle transition.
    progress.loading('op-199', false);
    await nextTick();
    expect(runs).toBe(3);
    expect(lastComplete).toBe(true);
});
