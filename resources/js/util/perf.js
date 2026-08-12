const STORAGE_KEY = 'statamic.perf';
const SNAPSHOT_VERSION = 1;
const PHASE_ORDER = { mount: 0, save: 1, interact: 2, other: 3 };
const MOUNT_SETTLE_MS = 200;
const TABLE_COLUMNS = ['phase', 'name', 'heat', 'count', 'total', 'mean', 'p95', 'max'];

const samples = new Map();
const counts = new Map();
const startStacks = new Map();

let enabled = false;
let seq = 0;
let observer = null;
let vueApp = null;

let currentPhase = null;
let phaseStartedAt = null;
let phaseBeforeSave = null;
let mountEnded = true;
let endMountTimer = null;

function isStorageEnabled() {
    try {
        return typeof localStorage !== 'undefined' && localStorage.getItem(STORAGE_KEY) === '1';
    } catch {
        return false;
    }
}

function scoped(name) {
    return currentPhase ? `${currentPhase}.${name}` : name;
}

function record(name, duration) {
    const key = scoped(name);

    if (!samples.has(key)) {
        samples.set(key, []);
    }

    samples.get(key).push(duration);
}

function recordGlobal(name, duration) {
    if (!samples.has(name)) {
        samples.set(name, []);
    }

    samples.get(name).push(duration);
}

function percentile(sorted, p) {
    if (sorted.length === 0) return 0;
    const index = Math.min(sorted.length - 1, Math.ceil((p / 100) * sorted.length) - 1);
    return sorted[index];
}

function summarize(name, values, countOnly = false) {
    if (countOnly || values.length === 0) {
        return {
            name,
            count: counts.get(name) || values.length,
            total: 0,
            mean: 0,
            p95: 0,
            max: 0,
        };
    }

    const sorted = [...values].sort((a, b) => a - b);
    const total = values.reduce((sum, value) => sum + value, 0);

    return {
        name,
        count: values.length,
        total: Number(total.toFixed(3)),
        mean: Number((total / values.length).toFixed(3)),
        p95: Number(percentile(sorted, 95).toFixed(3)),
        max: Number(sorted[sorted.length - 1].toFixed(3)),
    };
}

function parsePhase(name) {
    // Wall-clock spans recorded when leaving a phase (headline metrics).
    if (name.startsWith('phase.')) {
        const phase = name.slice('phase.'.length);
        if (PHASE_ORDER[phase] !== undefined) {
            return { phase, metric: name, wall: true };
        }
    }

    if (name.startsWith('phase.enter.')) {
        return { phase: 'other', metric: name, wall: false };
    }

    const match = name.match(/^(mount|save|interact)\.(.+)$/);
    if (match) {
        return { phase: match[1], metric: match[2], wall: false };
    }

    return { phase: 'other', metric: name, wall: false };
}

function startObserver() {
    if (observer || typeof PerformanceObserver === 'undefined') return;

    const types = [];

    try {
        if (PerformanceObserver.supportedEntryTypes?.includes('longtask')) {
            types.push('longtask');
        }
        if (PerformanceObserver.supportedEntryTypes?.includes('event')) {
            types.push('event');
        }
    } catch {
        // Some environments throw when reading supportedEntryTypes.
    }

    if (types.length === 0) return;

    observer = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
            if (entry.entryType === 'longtask') {
                record('browser.longtask', entry.duration);
            } else if (entry.entryType === 'event') {
                record(`browser.event.${entry.name}`, entry.duration);
            }
        }
    });

    try {
        observer.observe({ entryTypes: types });
    } catch {
        observer = null;
    }
}

function stopObserver() {
    observer?.disconnect();
    observer = null;
}

function applyVuePerformance(value) {
    if (vueApp?.config) {
        vueApp.config.performance = value;
    }
}

function enterPhase(name) {
    if (!enabled || !name) return api;

    if (currentPhase && phaseStartedAt != null) {
        recordGlobal(`phase.${currentPhase}`, performance.now() - phaseStartedAt);
    }

    currentPhase = name;
    phaseStartedAt = performance.now();
    counts.set(`phase.enter.${name}`, (counts.get(`phase.enter.${name}`) || 0) + 1);

    return api;
}

function endMount() {
    if (!enabled || mountEnded || currentPhase !== 'mount') return api;

    mountEnded = true;

    if (endMountTimer) {
        clearTimeout(endMountTimer);
        endMountTimer = null;
    }

    enterPhase('interact');

    return api;
}

function notifyMountActivity() {
    if (!enabled || mountEnded || currentPhase !== 'mount') return api;

    if (endMountTimer) {
        clearTimeout(endMountTimer);
    }

    endMountTimer = setTimeout(() => {
        endMountTimer = null;
        endMount();
    }, MOUNT_SETTLE_MS);

    return api;
}

function beginSave() {
    if (!enabled) return api;

    phaseBeforeSave = currentPhase || 'interact';
    enterPhase('save');

    return api;
}

function endSave() {
    if (!enabled) return api;

    if (currentPhase !== 'save') return api;

    const next = phaseBeforeSave || 'interact';
    phaseBeforeSave = null;
    enterPhase(next);

    return api;
}

function getPhase() {
    return currentPhase;
}

function enable(options = {}) {
    enabled = true;

    try {
        localStorage.setItem(STORAGE_KEY, '1');
    } catch {
        // Ignore storage failures (private mode, SSR, etc).
    }

    applyVuePerformance(true);
    startObserver();

    const initialPhase = options.phase ?? 'interact';
    currentPhase = null;
    phaseStartedAt = null;
    phaseBeforeSave = null;
    mountEnded = initialPhase !== 'mount';

    if (endMountTimer) {
        clearTimeout(endMountTimer);
        endMountTimer = null;
    }

    enterPhase(initialPhase);

    return api;
}

function disable() {
    enabled = false;

    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch {
        // Ignore storage failures.
    }

    applyVuePerformance(false);
    stopObserver();

    currentPhase = null;
    phaseStartedAt = null;
    phaseBeforeSave = null;
    mountEnded = true;

    if (endMountTimer) {
        clearTimeout(endMountTimer);
        endMountTimer = null;
    }

    return api;
}

function reset() {
    samples.clear();
    counts.clear();
    startStacks.clear();

    if (typeof performance !== 'undefined' && performance.clearMarks && performance.clearMeasures) {
        try {
            performance.clearMarks();
            performance.clearMeasures();
        } catch {
            // Ignore — some browsers restrict clear* to same-origin marks.
        }
    }

    return api;
}

function measure(name, fn) {
    if (!enabled) {
        return fn();
    }

    const fullName = scoped(name);
    const id = `${fullName}:${++seq}`;
    const startMark = `${id}:start`;
    const endMark = `${id}:end`;

    performance.mark(startMark);

    try {
        return fn();
    } finally {
        performance.mark(endMark);
        try {
            performance.measure(fullName, startMark, endMark);
            const entries = performance.getEntriesByName(fullName, 'measure');
            const entry = entries[entries.length - 1];
            if (entry) {
                // Already scoped via fullName — record without double-prefixing.
                if (!samples.has(fullName)) {
                    samples.set(fullName, []);
                }
                samples.get(fullName).push(entry.duration);
            }
        } catch {
            // Ignore measure failures.
        } finally {
            try {
                performance.clearMarks(startMark);
                performance.clearMarks(endMark);
                performance.clearMeasures(fullName);
            } catch {
                // Ignore cleanup failures.
            }
        }
    }
}

function start(name) {
    if (!enabled) return;

    // Stack is keyed by logical name so stop() still works if the phase flips mid-span
    // (common for bard.mount finishing after mount → interact).
    const fullName = scoped(name);
    const id = `${fullName}:${++seq}`;
    const startMark = `${id}:start`;

    performance.mark(startMark);

    if (!startStacks.has(name)) {
        startStacks.set(name, []);
    }

    startStacks.get(name).push({ id, startMark, startedAt: performance.now(), fullName });
}

function stop(name) {
    if (!enabled) return;

    const stack = startStacks.get(name);
    if (!stack?.length) return;

    const { startMark, startedAt, fullName } = stack.pop();
    const endMark = `${fullName}:${++seq}:end`;

    performance.mark(endMark);

    try {
        performance.measure(fullName, startMark, endMark);
        const entries = performance.getEntriesByName(fullName, 'measure');
        const entry = entries[entries.length - 1];
        if (!samples.has(fullName)) {
            samples.set(fullName, []);
        }
        samples.get(fullName).push(entry ? entry.duration : performance.now() - startedAt);
    } catch {
        if (!samples.has(fullName)) {
            samples.set(fullName, []);
        }
        samples.get(fullName).push(performance.now() - startedAt);
    } finally {
        try {
            performance.clearMarks(startMark);
            performance.clearMarks(endMark);
            performance.clearMeasures(fullName);
        } catch {
            // Ignore cleanup failures.
        }
    }
}

function count(name) {
    if (!enabled) return;

    const key = scoped(name);
    counts.set(key, (counts.get(key) || 0) + 1);
}

/**
 * Humanize durations for console output. Raw export/diff data stays in ms.
 * < 1000 → "12.3ms"   ≥ 1000 → "1s" / "1.5s"   ≥ 60s → "1m 2s"
 */
function formatDuration(ms) {
    if (ms == null || Number.isNaN(Number(ms))) return String(ms);

    const value = Number(ms);
    const abs = Math.abs(value);
    const sign = value < 0 ? '-' : '';

    if (abs < 1000) {
        return `${value}ms`;
    }

    const seconds = abs / 1000;

    if (seconds >= 60) {
        const mins = Math.floor(seconds / 60);
        const rem = seconds - mins * 60;

        if (rem < 0.05) {
            return `${sign}${mins}m`;
        }

        const remStr = rem >= 10 ? rem.toFixed(0) : rem.toFixed(1).replace(/\.0$/, '');
        return `${sign}${mins}m ${remStr}s`;
    }

    // 1 → "1s", 1.5 → "1.5s", 1.234 → "1.23s", 12.34 → "12.3s"
    const decimals = seconds >= 10 ? 1 : 2;
    const trimmed = seconds.toFixed(decimals).replace(/\.?0+$/, '');

    return `${sign}${trimmed}s`;
}

function heatFor(row) {
    // Count-only tallies have no duration — not a latency signal.
    if (!row.total && !row.mean) {
        return { level: 'count', label: 'count', color: '#94a3b8' };
    }

    // Wall-clock phase spans are judged by total, not mean (n=1).
    if (row.name.startsWith('phase.') && row.count === 1) {
        if (row.total >= 1000) {
            return { level: 'critical', label: 'critical', color: '#ef4444' };
        }
        if (row.total >= 300) {
            return { level: 'hot', label: 'hot', color: '#f97316' };
        }
        if (row.total >= 100) {
            return { level: 'warm', label: 'warm', color: '#eab308' };
        }
        return { level: 'ok', label: 'ok', color: '#22c55e' };
    }

    // Color by per-call mean (ms). 16ms ≈ one animation frame.
    if (row.mean >= 16 || row.p95 >= 32) {
        return { level: 'critical', label: 'critical', color: '#ef4444' };
    }
    if (row.mean >= 8 || row.p95 >= 16) {
        return { level: 'hot', label: 'hot', color: '#f97316' };
    }
    if (row.mean >= 2 || row.total >= 100) {
        return { level: 'warm', label: 'warm', color: '#eab308' };
    }

    return { level: 'ok', label: 'ok', color: '#22c55e' };
}

function reportJson() {
    const rows = [];

    for (const [name, values] of samples.entries()) {
        rows.push(summarize(name, values));
    }

    for (const [name, value] of counts.entries()) {
        if (!samples.has(name)) {
            rows.push(summarize(name, [], true));
            rows[rows.length - 1].count = value;
        }
    }

    return rows
        .map((row) => {
            const { phase, metric, wall } = parsePhase(row.name);
            return { ...row, phase, metric, wall, heat: heatFor(row).label };
        })
        .sort((a, b) => {
            const phaseDelta = (PHASE_ORDER[a.phase] ?? 99) - (PHASE_ORDER[b.phase] ?? 99);
            if (phaseDelta !== 0) return phaseDelta;
            // Phase wall clocks first within each group.
            if (a.wall !== b.wall) return a.wall ? -1 : 1;
            return b.total - a.total || b.count - a.count || a.name.localeCompare(b.name);
        });
}

function report() {
    const rows = reportJson();

    console.log(
        '%cStatamic $perf report%c  (≥1000ms shown as s)  grouped by phase: mount → save → interact',
        'font-weight:700;font-size:12px',
        'color:#64748b',
    );
    console.log(
        '%cHeadline metrics:%c  phase.mount (initial render)   phase.save (full save pipeline)',
        'font-weight:700',
        'color:#64748b',
    );
    console.log(
        '%c● critical%c mean≥16 or p95≥32   %c● hot%c mean≥8 or p95≥16   %c● warm%c mean≥2 or total≥100   %c● ok%c   %c● count%c tally only',
        'color:#ef4444;font-weight:700',
        'color:#64748b',
        'color:#f97316;font-weight:700',
        'color:#64748b',
        'color:#eab308;font-weight:700',
        'color:#64748b',
        'color:#22c55e;font-weight:700',
        'color:#64748b',
        'color:#94a3b8;font-weight:700',
        'color:#64748b',
    );

    let lastPhase = null;

    for (const row of rows) {
        if (row.phase !== lastPhase) {
            lastPhase = row.phase;
            const label = row.phase === 'other' ? 'OTHER' : row.phase.toUpperCase();
            console.log(`%c—— ${label} ——`, 'color:#94a3b8;font-weight:700;margin-top:6px');
        }

        const heat = heatFor(row);
        const stats =
            heat.level === 'count'
                ? `count=${row.count}`
                : `mean=${formatDuration(row.mean)}  p95=${formatDuration(row.p95)}  max=${formatDuration(row.max)}  total=${formatDuration(row.total)}  n=${row.count}`;

        console.log(
            `%c${heat.label.padEnd(8)}%c ${row.name}  %c${stats}`,
            `color:${heat.color};font-weight:700`,
            'color:inherit;font-weight:600',
            'color:#64748b',
        );
    }

    // DevTools preview — for actual copy/paste use copy() / download() (raw ms).
    console.table(
        rows.map((row) => ({
            heat: row.heat,
            phase: row.phase,
            name: row.name,
            count: row.count,
            total: formatDuration(row.total),
            mean: formatDuration(row.mean),
            p95: formatDuration(row.p95),
            max: formatDuration(row.max),
        })),
    );

    console.log(
        '%cExport%c  Statamic.$perf.copy()  // tsv   ·  .copy("md")  ·  .copy("json")  ·  .download()',
        'font-weight:700',
        'color:#64748b',
    );
    console.log(
        '%cCompare%c  const before = Statamic.$perf.snapshot("before");  /* later */  Statamic.$perf.diff(before)',
        'font-weight:700',
        'color:#64748b',
    );

    return rows;
}

function snapshot(label = null) {
    return {
        version: SNAPSHOT_VERSION,
        label,
        capturedAt: new Date().toISOString(),
        url: typeof location !== 'undefined' ? location.href : null,
        userAgent: typeof navigator !== 'undefined' ? navigator.userAgent : null,
        phase: currentPhase,
        rows: reportJson(),
    };
}

function normalizeSnapshot(input) {
    if (!input) {
        throw new Error('Expected a $perf.snapshot() object, JSON string, or rows array');
    }

    if (typeof input === 'string') {
        input = JSON.parse(input);
    }

    if (Array.isArray(input)) {
        return { version: SNAPSHOT_VERSION, label: null, rows: input };
    }

    if (Array.isArray(input.rows)) {
        return input;
    }

    throw new Error('Expected a $perf.snapshot() object, JSON string, or rows array');
}

function cellValue(row, column) {
    if (column === 'total' || column === 'mean' || column === 'p95' || column === 'max') {
        return row[column];
    }

    return row[column] ?? '';
}

function escapeCsv(value) {
    const str = String(value ?? '');
    if (/[",\n\r]/.test(str)) {
        return `"${str.replace(/"/g, '""')}"`;
    }
    return str;
}

function toTsv(input) {
    const rows = Array.isArray(input) ? input : (input?.rows ?? reportJson());
    const lines = [TABLE_COLUMNS.join('\t')];

    for (const row of rows) {
        lines.push(TABLE_COLUMNS.map((column) => cellValue(row, column)).join('\t'));
    }

    return lines.join('\n');
}

function toCsv(input) {
    const rows = Array.isArray(input) ? input : (input?.rows ?? reportJson());
    const lines = [TABLE_COLUMNS.join(',')];

    for (const row of rows) {
        lines.push(TABLE_COLUMNS.map((column) => escapeCsv(cellValue(row, column))).join(','));
    }

    return lines.join('\n');
}

function escapeMarkdownCell(value) {
    return String(value ?? '')
        .replace(/\\/g, '\\\\')
        .replace(/\|/g, '\\|')
        .replace(/`/g, "'");
}

function toMarkdown(input) {
    const rows = Array.isArray(input) ? input : (input?.rows ?? reportJson());
    const header = '| phase | name | heat | count | total (ms) | mean (ms) | p95 (ms) | max (ms) |';
    const sep = '| --- | --- | --- | ---: | ---: | ---: | ---: | ---: |';
    const body = rows.map((row) => {
        const name = escapeMarkdownCell(row.name);
        const phase = escapeMarkdownCell(row.phase);
        const heat = escapeMarkdownCell(row.heat);

        return `| ${phase} | \`${name}\` | ${heat} | ${row.count ?? 0} | ${row.total ?? 0} | ${row.mean ?? 0} | ${row.p95 ?? 0} | ${row.max ?? 0} |`;
    });

    return [header, sep, ...body].join('\n');
}

function serialize(format = 'tsv', input) {
    const normalized = format.toLowerCase();

    if (normalized === 'json') {
        const data = input ? normalizeSnapshot(input) : snapshot();
        return JSON.stringify(data, null, 2);
    }

    if (normalized === 'md' || normalized === 'markdown') {
        return toMarkdown(input);
    }

    if (normalized === 'csv') {
        return toCsv(input);
    }

    if (normalized === 'tsv' || normalized === 'table') {
        return toTsv(input);
    }

    throw new Error(`Unknown export format "${format}". Use tsv, csv, md, or json.`);
}

async function copy(format = 'tsv') {
    const text = serialize(format);
    const rows = reportJson().length;

    // Clipboard API often rejects when invoked from the console without a user
    // gesture (NotAllowedError) — fall back to dumping the string.
    try {
        if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);
            console.log(`%cCopied%c $perf ${format} (${rows} rows) to clipboard`, 'font-weight:700;color:#22c55e', '');
            return text;
        }
    } catch (error) {
        console.warn(`$perf.copy(${JSON.stringify(format)}) clipboard write failed:`, error);
    }

    console.log(`%cClipboard unavailable%c — copy the string below:`, 'font-weight:700;color:#f97316', '');
    console.log(text);

    return text;
}

function download(format = 'json', filename) {
    const text = serialize(format);
    const ext = format === 'markdown' || format === 'md' ? 'md' : format;
    const stamp = new Date().toISOString().replace(/[:.]/g, '-');
    const name = filename || `statamic-perf-${stamp}.${ext === 'tsv' ? 'tsv' : ext}`;

    if (typeof document === 'undefined') {
        console.log(text);
        return text;
    }

    const mime =
        ext === 'json'
            ? 'application/json'
            : ext === 'csv'
              ? 'text/csv'
              : ext === 'md'
                ? 'text/markdown'
                : 'text/tab-separated-values';

    const blob = new Blob([text], { type: `${mime};charset=utf-8` });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.download = name;
    anchor.click();
    URL.revokeObjectURL(url);

    console.log(`%cDownloaded%c ${name}`, 'font-weight:700;color:#22c55e', '');

    return text;
}

function pctDelta(before, after) {
    if (before == null || after == null) return null;
    if (before === 0) return after === 0 ? 0 : null;
    return ((after - before) / before) * 100;
}

function diff(baseline, current) {
    const beforeSnap = normalizeSnapshot(baseline);
    const afterSnap = normalizeSnapshot(current ?? snapshot());

    const beforeMap = new Map(beforeSnap.rows.map((row) => [row.name, row]));
    const afterMap = new Map(afterSnap.rows.map((row) => [row.name, row]));
    const names = [...new Set([...beforeMap.keys(), ...afterMap.keys()])].sort((a, b) => {
        const aPhase = parsePhase(a).phase;
        const bPhase = parsePhase(b).phase;
        const phaseDelta = (PHASE_ORDER[aPhase] ?? 99) - (PHASE_ORDER[bPhase] ?? 99);
        if (phaseDelta !== 0) return phaseDelta;
        return a.localeCompare(b);
    });

    const rows = names.map((name) => {
        const before = beforeMap.get(name);
        const after = afterMap.get(name);
        const totalBefore = before?.total ?? null;
        const totalAfter = after?.total ?? null;
        const meanBefore = before?.mean ?? null;
        const meanAfter = after?.mean ?? null;
        const deltaTotal = totalBefore != null && totalAfter != null ? Number((totalAfter - totalBefore).toFixed(3)) : null;
        const deltaMean = meanBefore != null && meanAfter != null ? Number((meanAfter - meanBefore).toFixed(3)) : null;
        const pctTotal = pctDelta(totalBefore, totalAfter);
        const pctMean = pctDelta(meanBefore, meanAfter);

        let verdict = 'same';
        if (!before) verdict = 'added';
        else if (!after) verdict = 'removed';
        else if (deltaTotal > 0.01 || (pctTotal != null && pctTotal >= 3)) verdict = 'slower';
        else if (deltaTotal < -0.01 || (pctTotal != null && pctTotal <= -3)) verdict = 'faster';

        return {
            verdict,
            phase: after?.phase ?? before?.phase ?? parsePhase(name).phase,
            name,
            'total before': totalBefore,
            'total after': totalAfter,
            'Δ total': deltaTotal,
            'Δ total %': pctTotal == null ? null : Number(pctTotal.toFixed(1)),
            'mean before': meanBefore,
            'mean after': meanAfter,
            'Δ mean': deltaMean,
            'Δ mean %': pctMean == null ? null : Number(pctMean.toFixed(1)),
            'count before': before?.count ?? null,
            'count after': after?.count ?? null,
        };
    });

    const slower = rows.filter((row) => row.verdict === 'slower').length;
    const faster = rows.filter((row) => row.verdict === 'faster').length;
    const added = rows.filter((row) => row.verdict === 'added').length;
    const removed = rows.filter((row) => row.verdict === 'removed').length;

    console.log(
        `%cStatamic $perf diff%c  ${beforeSnap.label || beforeSnap.capturedAt || 'baseline'} → ${afterSnap.label || afterSnap.capturedAt || 'current'}`,
        'font-weight:700;font-size:12px',
        'color:#64748b',
    );
    console.log(
        `%c${faster} faster%c · %c${slower} slower%c · ${added} added · ${removed} removed · ${rows.length} metrics`,
        'color:#22c55e;font-weight:700',
        'color:#64748b',
        'color:#ef4444;font-weight:700',
        'color:#64748b',
    );
    console.table(rows);
    console.log(
        '%cTip%c  copy the diff: Statamic.$perf.copyDiff(baseline)  // or pass the object you just diffed against',
        'font-weight:700',
        'color:#64748b',
    );

    return {
        baseline: beforeSnap,
        current: afterSnap,
        summary: { faster, slower, added, removed, total: rows.length },
        rows,
    };
}

async function copyDiff(baseline, current) {
    const result = diff(baseline, current);
    const header = [
        'verdict',
        'phase',
        'name',
        'total_before',
        'total_after',
        'delta_total',
        'delta_total_pct',
        'mean_before',
        'mean_after',
        'delta_mean',
        'delta_mean_pct',
        'count_before',
        'count_after',
    ];
    const lines = [
        header.join('\t'),
        ...result.rows.map((row) =>
            [
                row.verdict,
                row.phase,
                row.name,
                row['total before'],
                row['total after'],
                row['Δ total'],
                row['Δ total %'],
                row['mean before'],
                row['mean after'],
                row['Δ mean'],
                row['Δ mean %'],
                row['count before'],
                row['count after'],
            ].join('\t'),
        ),
    ];
    const text = lines.join('\n');

    try {
        if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(text);
            console.log(
                `%cCopied%c $perf diff TSV (${result.rows.length} rows) to clipboard`,
                'font-weight:700;color:#22c55e',
                '',
            );
            return text;
        }
    } catch (error) {
        console.warn('$perf.copyDiff() clipboard write failed:', error);
    }

    console.log(text);

    return text;
}

function isEnabled() {
    return enabled;
}

function attachVueApp(app) {
    vueApp = app;
    if (enabled) {
        applyVuePerformance(true);
    }
}

const api = {
    enable,
    disable,
    reset,
    measure,
    start,
    stop,
    count,
    enterPhase,
    endMount,
    notifyMountActivity,
    beginSave,
    endSave,
    getPhase,
    report,
    reportJson,
    snapshot,
    toTsv,
    toCsv,
    toMarkdown,
    serialize,
    copy,
    download,
    diff,
    copyDiff,
    isEnabled,
    attachVueApp,
    STORAGE_KEY,
    MOUNT_SETTLE_MS,
    SNAPSHOT_VERSION,
    formatDuration,
};

if (isStorageEnabled()) {
    enable({ phase: 'mount' });
}

export default api;
export { api as perf };
