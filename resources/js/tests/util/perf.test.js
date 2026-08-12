import { afterEach, beforeEach, expect, test, vi } from 'vitest';
import perf from '@/util/perf.js';

beforeEach(() => {
    localStorage.removeItem(perf.STORAGE_KEY);
    perf.disable();
    perf.reset();
});

afterEach(() => {
    perf.disable();
    perf.reset();
});

test('is a no-op when disabled', () => {
    const spy = vi.fn(() => 42);

    expect(perf.isEnabled()).toBe(false);
    expect(perf.measure('noop', spy)).toBe(42);
    expect(spy).toHaveBeenCalledOnce();
    expect(perf.reportJson()).toEqual([]);
});

test('records measure samples when enabled', () => {
    perf.enable();

    perf.measure('demo.work', () => {
        let total = 0;
        for (let i = 0; i < 1000; i++) total += i;
        return total;
    });

    const report = perf.reportJson();
    expect(report).toHaveLength(2); // phase.enter.interact count + sample
    expect(report.find((row) => row.name === 'interact.demo.work')).toBeTruthy();
    expect(report.find((row) => row.name === 'interact.demo.work').count).toBe(1);
});

test('start/stop records a sample', () => {
    perf.enable();

    perf.start('demo.span');
    perf.stop('demo.span');

    const row = perf.reportJson().find((item) => item.name === 'interact.demo.span');
    expect(row).toBeTruthy();
    expect(row.count).toBe(1);
});

test('count increments without duration samples', () => {
    perf.enable();

    perf.count('demo.events');
    perf.count('demo.events');

    const row = perf.reportJson().find((item) => item.name === 'interact.demo.events');
    expect(row.count).toBe(2);
});

test('enable persists to localStorage and disable clears it', () => {
    perf.enable();
    expect(localStorage.getItem(perf.STORAGE_KEY)).toBe('1');
    expect(perf.isEnabled()).toBe(true);

    perf.disable();
    expect(localStorage.getItem(perf.STORAGE_KEY)).toBeNull();
    expect(perf.isEnabled()).toBe(false);
});

test('reset clears recorded samples', () => {
    perf.enable();
    perf.count('demo.reset');
    perf.measure('demo.reset.measure', () => 1);

    expect(perf.reportJson().length).toBeGreaterThan(0);

    perf.reset();
    expect(perf.reportJson()).toEqual([]);
});

test('reportJson includes heat, phase, and sorts mount before save before interact', () => {
    perf.enable({ phase: 'mount' });

    perf.measure('demo.cheap', () => {});
    perf.endMount();

    perf.beginSave();
    for (let i = 0; i < 5; i++) {
        perf.start('demo.pricey');
        const end = performance.now() + 1;
        while (performance.now() < end) {
            // busy wait ~1ms so mean/total are non-zero
        }
        perf.stop('demo.pricey');
    }
    perf.endSave();
    perf.count('demo.events');

    const rows = perf.reportJson();

    expect(rows.every((row) => typeof row.heat === 'string')).toBe(true);
    expect(rows.every((row) => typeof row.phase === 'string')).toBe(true);

    const mountIdx = rows.findIndex((row) => row.name === 'mount.demo.cheap');
    const saveIdx = rows.findIndex((row) => row.name === 'save.demo.pricey');
    const interactIdx = rows.findIndex((row) => row.name === 'interact.demo.events');

    expect(mountIdx).toBeGreaterThanOrEqual(0);
    expect(saveIdx).toBeGreaterThan(mountIdx);
    expect(interactIdx).toBeGreaterThan(saveIdx);
});

test('endMount records phase.mount and switches to interact', async () => {
    perf.enable({ phase: 'mount' });
    expect(perf.getPhase()).toBe('mount');

    perf.measure('during.mount', () => 1);
    perf.endMount();

    expect(perf.getPhase()).toBe('interact');

    const rows = perf.reportJson();
    const wall = rows.find((row) => row.name === 'phase.mount');
    expect(wall).toBeTruthy();
    expect(wall.wall).toBe(true);
    expect(rows.find((row) => row.name === 'mount.during.mount')).toBeTruthy();
});

test('beginSave/endSave records phase.save and restores prior phase', () => {
    perf.enable({ phase: 'interact' });

    perf.beginSave();
    expect(perf.getPhase()).toBe('save');
    perf.measure('publish.save.request', () => 1);
    perf.endSave();

    expect(perf.getPhase()).toBe('interact');

    const rows = perf.reportJson();
    expect(rows.find((row) => row.name === 'phase.save')).toBeTruthy();
    expect(rows.find((row) => row.name === 'save.publish.save.request')).toBeTruthy();
});

test('start/stop keeps the phase from when the span began', () => {
    perf.enable({ phase: 'mount' });

    perf.start('bard.mount');
    perf.endMount();
    expect(perf.getPhase()).toBe('interact');
    perf.stop('bard.mount');

    const rows = perf.reportJson();
    expect(rows.find((row) => row.name === 'mount.bard.mount')).toBeTruthy();
    expect(rows.find((row) => row.name === 'interact.bard.mount')).toBeFalsy();
});

test('notifyMountActivity ends mount after settle delay', async () => {
    vi.useFakeTimers();
    perf.enable({ phase: 'mount' });

    perf.notifyMountActivity();
    expect(perf.getPhase()).toBe('mount');

    vi.advanceTimersByTime(perf.MOUNT_SETTLE_MS - 1);
    expect(perf.getPhase()).toBe('mount');

    vi.advanceTimersByTime(1);
    expect(perf.getPhase()).toBe('interact');
    expect(perf.reportJson().find((row) => row.name === 'phase.mount')).toBeTruthy();

    vi.useRealTimers();
});

test('snapshot and markdown/tsv exports are pasteable', () => {
    perf.enable();
    perf.measure('demo.export', () => 1);

    const snap = perf.snapshot('unit');
    expect(snap.version).toBe(perf.SNAPSHOT_VERSION);
    expect(snap.label).toBe('unit');
    expect(snap.rows.some((row) => row.name === 'interact.demo.export')).toBe(true);

    const md = perf.toMarkdown();
    expect(md).toContain('| phase | name |');
    expect(md).toContain('`interact.demo.export`');

    const tsv = perf.toTsv();
    expect(tsv.split('\n')[0]).toBe('phase\tname\theat\tcount\ttotal\tmean\tp95\tmax');
    expect(tsv).toContain('interact.demo.export');
});

test('markdown export escapes backslashes before pipes in metric names', () => {
    perf.enable();
    perf.count('demo\\pipe|name');

    const md = perf.toMarkdown();
    expect(md).toContain('`interact.demo\\\\pipe\\|name`');
});

test('copy writes export text to the clipboard', async () => {
    const writeText = vi.fn().mockResolvedValue(undefined);
    vi.stubGlobal('navigator', { clipboard: { writeText } });

    perf.enable();
    perf.count('demo.copy');

    const text = await perf.copy('md');
    expect(writeText).toHaveBeenCalledOnce();
    expect(writeText.mock.calls[0][0]).toContain('`interact.demo.copy`');
    expect(text).toBe(writeText.mock.calls[0][0]);

    vi.unstubAllGlobals();
});

test('copy falls back to console when clipboard write is denied', async () => {
    const writeText = vi.fn().mockRejectedValue(new Error('NotAllowedError'));
    const log = vi.spyOn(console, 'log').mockImplementation(() => {});
    const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});
    vi.stubGlobal('navigator', { clipboard: { writeText } });

    perf.enable();
    perf.count('demo.copy.fallback');

    const text = await perf.copy('md');
    expect(writeText).toHaveBeenCalledOnce();
    expect(text).toContain('`interact.demo.copy.fallback`');
    expect(warn).toHaveBeenCalled();
    expect(log).toHaveBeenCalledWith(text);

    log.mockRestore();
    warn.mockRestore();
    vi.unstubAllGlobals();
});

test('formatDuration scales ms to s (and m) for readability', () => {
    expect(perf.formatDuration(0)).toBe('0ms');
    expect(perf.formatDuration(16)).toBe('16ms');
    expect(perf.formatDuration(999)).toBe('999ms');
    expect(perf.formatDuration(1000)).toBe('1s');
    expect(perf.formatDuration(1500)).toBe('1.5s');
    expect(perf.formatDuration(1234)).toBe('1.23s');
    expect(perf.formatDuration(12345)).toBe('12.3s');
    expect(perf.formatDuration(60000)).toBe('1m');
    expect(perf.formatDuration(62500)).toBe('1m 2.5s');
    expect(perf.formatDuration(-1500)).toBe('-1.5s');
});

test('diff compares snapshots and reports slower/faster', () => {
    const baseline = {
        version: 1,
        label: 'before',
        rows: [
            { name: 'phase.mount', phase: 'mount', heat: 'hot', count: 1, total: 100, mean: 100, p95: 100, max: 100, wall: true },
            { name: 'mount.demo.work', phase: 'mount', heat: 'ok', count: 2, total: 10, mean: 5, p95: 5, max: 5, wall: false },
        ],
    };

    const current = {
        version: 1,
        label: 'after',
        rows: [
            { name: 'phase.mount', phase: 'mount', heat: 'critical', count: 1, total: 200, mean: 200, p95: 200, max: 200, wall: true },
            { name: 'mount.demo.work', phase: 'mount', heat: 'ok', count: 2, total: 8, mean: 4, p95: 4, max: 4, wall: false },
            { name: 'mount.demo.new', phase: 'mount', heat: 'ok', count: 1, total: 1, mean: 1, p95: 1, max: 1, wall: false },
        ],
    };

    const result = perf.diff(baseline, current);

    expect(result.summary.slower).toBe(1);
    expect(result.summary.faster).toBe(1);
    expect(result.summary.added).toBe(1);

    const mount = result.rows.find((row) => row.name === 'phase.mount');
    expect(mount.verdict).toBe('slower');
    expect(mount['Δ total']).toBe(100);
    expect(mount['Δ total %']).toBe(100);
});
