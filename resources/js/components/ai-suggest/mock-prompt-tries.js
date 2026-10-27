import { steer, suggestFor } from './mock-suggestions.js';

// Prototype: what a page prompt would do, for trying it out on Settings → AI. Nothing here is real, and nothing is saved.
// A page prompt either reports back (findings, quoting the sentence each is in) or changes some fields (each shown as a diff).

const sentences = (text) => text.split(/(?<=[.!?])\s+/).map((s) => s.trim()).filter(Boolean);

const isLong = (field) => ['textarea', 'markdown'].includes(field.type) || field.value.length > 80;

// The kinds of claim a legal check would flag, and what it would say about each
const claims = [
    { match: /\b\d+\s?%|\b(half price|£\d+|\$\d+)/i, note: 'A price claim. Check it matches the terms of the offer.' },
    { match: /\b(guarantee[ds]?|warrant(y|ies)|money back)\b/i, note: 'A guarantee needs terms and conditions.' },
    { match: /\b(best|biggest|lowest|cheapest|only|first|number one|no\.? ?1)\b/i, note: "A claim we'd need evidence for." },
    { match: /\b(ever|never|always|free)\b/i, note: 'Absolute wording. Can we back it up?' },
    { match: /\b(cures?|heals?|healthier|safe for)\b/i, note: 'A health or safety claim needs evidence.' },
];

function legalCheck(entry) {
    const findings = [];

    for (const field of entry.fields) {
        for (const quote of sentences(field.value)) {
            const claim = claims.find((claim) => claim.match.test(quote));
            if (claim) findings.push({ field: field.display, quote, note: claim.note });
        }
    }

    return {
        kind: 'findings',
        summary: findings.length ? __n('Found :count thing to check|Found :count things to check', findings.length) : __('Found nothing that needs backing up.'),
        items: findings,
    };
}

function seoCheck(entry) {
    const changes = entry.fields
        .filter((field) => /meta|seo/i.test(`${field.handle} ${field.display}`))
        .map((field) => {
            const limit = /title/i.test(field.handle) ? 60 : 155;
            if (field.value && field.value.length <= limit) return null;

            const suggested = suggestFor(field, field.value, { title: entry.title }).suggestions[0]?.text ?? '';
            return { field: field.display, from: field.value, to: suggested.slice(0, limit) };
        })
        .filter(Boolean);

    if (!entry.fields.some((field) => /meta|seo/i.test(`${field.handle} ${field.display}`))) {
        return { kind: 'findings', summary: __('This entry has no meta title or description fields.'), items: [] };
    }

    return {
        kind: 'changes',
        summary: changes.length ? __n('Would suggest :count change|Would suggest :count changes', changes.length) : __('The meta title and description are fine as they are.'),
        items: changes,
    };
}

function altText(entry) {
    return {
        kind: 'changes',
        summary: __n('Would suggest :count change|Would suggest :count changes', 1),
        items: [{ field: __('Hero image alt text'), from: '', to: `A photo for “${entry.title}”, showing the product in use.` }],
    };
}

// Anything else rewrites the entry's text, in the way the instructions ask
function rewrite(instructions, entry) {
    const changes = entry.fields
        .filter((field) => field.value.trim())
        .map((field) => {
            const to = steer({ long: isLong(field), suggestions: [{ text: field.value }] }, instructions).suggestions[0].text;
            return to === field.value ? null : { field: field.display, from: field.value, to };
        })
        .filter(Boolean);

    return {
        kind: 'changes',
        summary: changes.length ? __n('Would change :count field|Would change :count fields', changes.length) : __('Would leave everything as it is.'),
        items: changes,
    };
}

export function tryPagePrompt(instructions, entry) {
    const how = instructions.toLowerCase();

    if (/legal|claim/.test(how)) return legalCheck(entry);
    if (/meta|seo/.test(how)) return seoCheck(entry);
    if (/alt text|images?\b/.test(how)) return altText(entry);

    return rewrite(instructions, entry);
}
