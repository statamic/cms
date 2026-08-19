import { isPlainObject } from 'lodash-es';
import { KEYS } from './Constants.js';

// A never-mounted set body has its conditions evaluated by a headless watcher instead
// of by the Field components. That evaluation is only as reactive as the watcher's
// sources, so we work out up front what a set's conditions actually depend on.
//
// Every unrecognised shape has to fall into the slower branch. A set that watches too
// much is slow; a set that watches too little writes a stale `omitValue` and silently
// drops the value from the save payload.

const OUTSIDE_SET_RE = /^(\$root\.|root\.|\$parent\.)/;
const CUSTOM_CONDITION_RE = /^\s*custom\s/;

const cache = new WeakMap();

export default function analyzeSetConditions(config) {
    if (!isPlainObject(config)) return { needsRootValues: true, canDeferMount: false, hasRevealer: true };

    if (!cache.has(config)) cache.set(config, analyze(config));

    return cache.get(config);
}

function analyze(config) {
    const fields = fieldList(config.fields);

    if (fields === null) return { needsRootValues: true, canDeferMount: false, hasRevealer: true };

    let needsRootValues = false;
    let canDeferMount = true;
    let hasRevealer = false;

    fields.forEach((field) => {
        if (!isPlainObject(field)) {
            needsRootValues = true;
            canDeferMount = false;
            hasRevealer = true;
            return;
        }

        if (conditionsFor(field).some(dependsOutsideSet)) needsRootValues = true;

        // Revealers register themselves with the container when they mount, and that
        // registration changes how every other field's `omitValue` is worked out.
        if (field.type === 'revealer') {
            canDeferMount = false;
            hasRevealer = true;
        }

        const nested = nestedFields(field);

        // An unrecognised shape could be hiding anything, including a revealer.
        if (nested === null) {
            canDeferMount = false;
            hasRevealer = true;
            return;
        }

        if (nested.some((child) => child.type === 'revealer')) {
            canDeferMount = false;
            hasRevealer = true;
        }

        // Nothing evaluates the conditions of fields nested inside this set's fields,
        // so those only stay correct if the set actually mounts.
        if (nested.some((child) => conditionsFor(child).length > 0)) canDeferMount = false;
    });

    return { needsRootValues, canDeferMount, hasRevealer };
}

function conditionsFor(field) {
    return KEYS.filter((key) => field[key]).map((key) => field[key]);
}

function dependsOutsideSet(conditions) {
    // A bare string is a custom condition without a target. The callback is handed the
    // root values, so we have to assume it reads them.
    if (typeof conditions === 'string') return true;

    if (!isPlainObject(conditions)) return true;

    return Object.entries(conditions).some(([lhs, rhs]) => {
        if (typeof lhs !== 'string') return true;
        if (OUTSIDE_SET_RE.test(lhs)) return true;
        if (typeof rhs === 'string' && CUSTOM_CONDITION_RE.test(rhs)) return true;

        return !isScalar(rhs);
    });
}

// Every field config underneath this one, at any depth. Grids and groups keep theirs in
// `fields`, replicators and Bards in `sets` (either groups of sets, or bare sets).
// Returns null if anything along the way isn't a shape we recognise.
function nestedFields(field) {
    const found = [];
    const queue = [field];
    const seen = new Set();

    while (queue.length) {
        const current = queue.shift();

        if (seen.has(current)) continue;
        seen.add(current);

        if (!isPlainObject(current)) return null;

        const children = fieldList(current.fields);
        const sets = fieldList(current.sets);

        if (children === null || sets === null) return null;

        found.push(...children);
        queue.push(...children, ...sets);
    }

    return found;
}

function fieldList(fields) {
    if (fields === undefined || fields === null) return [];
    if (Array.isArray(fields)) return fields;
    if (isPlainObject(fields)) return Object.values(fields);

    return null;
}

function isScalar(value) {
    return value === null || ['string', 'number', 'boolean'].includes(typeof value);
}
