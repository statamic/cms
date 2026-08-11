#!/usr/bin/env node

/**
 * Diff two Vitest bench --outputJson snapshots.
 *
 * Usage:
 *   node scripts/bench-diff.mjs benchmarks/baseline.json benchmarks/results.json
 *   node scripts/bench-diff.mjs benchmarks/baseline.json   # reads benchmarks/results.json
 */

import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

const baselinePath = resolve(process.argv[2] || 'benchmarks/baseline.json');
const currentPath = resolve(process.argv[3] || 'benchmarks/results.json');

function load(path) {
    return JSON.parse(readFileSync(path, 'utf8'));
}

function flatten(report) {
    const rows = new Map();

    for (const file of report.files || []) {
        for (const group of file.groups || []) {
            for (const bench of group.benchmarks || []) {
                const key = `${group.fullName} › ${bench.name}`;
                rows.set(key, {
                    key,
                    name: bench.name,
                    group: group.fullName.replace(/^resources\/js\/tests\/browser\/bench\//, ''),
                    mean: bench.mean,
                    hz: bench.hz,
                    rme: bench.rme,
                    sampleCount: bench.sampleCount,
                });
            }
        }
    }

    return rows;
}

function fmtMs(value) {
    if (value >= 10) return value.toFixed(2);
    if (value >= 1) return value.toFixed(3);
    return value.toFixed(4);
}

function fmtPct(value) {
    const sign = value > 0 ? '+' : '';
    return `${sign}${value.toFixed(1)}%`;
}

function pad(value, width, right = false) {
    const str = String(value);
    return right ? str.padStart(width) : str.padEnd(width);
}

const baseline = flatten(load(baselinePath));
const current = flatten(load(currentPath));

const keys = [...new Set([...baseline.keys(), ...current.keys()])].sort();

if (keys.length === 0) {
    console.error('No benchmarks found in either report.');
    process.exit(1);
}

const rows = [];

for (const key of keys) {
    const before = baseline.get(key);
    const after = current.get(key);

    if (!before || !after) {
        rows.push({
            group: (after || before).group,
            name: (after || before).name,
            before: before?.mean ?? null,
            after: after?.mean ?? null,
            deltaPct: null,
            status: before ? 'removed' : 'added',
        });
        continue;
    }

    // Lower mean = faster. Positive deltaPct means slower than baseline.
    const deltaPct = ((after.mean - before.mean) / before.mean) * 100;
    let status = 'same';
    if (deltaPct <= -3) status = 'faster';
    else if (deltaPct >= 3) status = 'slower';

    rows.push({
        group: after.group,
        name: after.name,
        before: before.mean,
        after: after.mean,
        deltaPct,
        status,
    });
}

console.log(`Baseline: ${baselinePath}`);
console.log(`Current:  ${currentPath}`);
console.log('Delta = change in mean ms. Negative = faster than baseline.\n');

console.log(
    `${pad('Scenario', 52)} ${pad('baseline', 10, true)} ${pad('current', 10, true)} ${pad('Δ mean', 10, true)} ${pad('', 8)}`,
);
console.log('-'.repeat(94));

let group = null;
for (const row of rows) {
    if (row.group !== group) {
        if (group !== null) console.log('');
        group = row.group;
        console.log(group);
    }

    const label = `  ${row.name}`.slice(0, 52);
    if (row.status === 'added') {
        console.log(`${pad(label, 52)} ${pad('—', 10, true)} ${pad(fmtMs(row.after), 10, true)} ${pad('added', 10, true)}`);
        continue;
    }
    if (row.status === 'removed') {
        console.log(`${pad(label, 52)} ${pad(fmtMs(row.before), 10, true)} ${pad('—', 10, true)} ${pad('removed', 10, true)}`);
        continue;
    }

    const arrow = row.status === 'faster' ? 'faster' : row.status === 'slower' ? 'SLOWER' : '';
    console.log(
        `${pad(label, 52)} ${pad(fmtMs(row.before), 10, true)} ${pad(fmtMs(row.after), 10, true)} ${pad(fmtPct(row.deltaPct), 10, true)} ${arrow}`,
    );
}

const slower = rows.filter((row) => row.status === 'slower');
const faster = rows.filter((row) => row.status === 'faster');

console.log('\nSummary');
console.log(`  ${faster.length} faster (≥3%)`);
console.log(`  ${slower.length} slower (≥3%)`);
console.log(`  ${rows.length - faster.length - slower.length} within noise`);

if (slower.length) {
    console.log('\nRegressions:');
    for (const row of slower) {
        console.log(`  - ${row.name}: ${fmtPct(row.deltaPct)} (mean ${fmtMs(row.before)} → ${fmtMs(row.after)} ms)`);
    }
    process.exitCode = 1;
}
