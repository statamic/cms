/**
 * Tiny seeded PRNG so fixture generation is deterministic across runs.
 */
export function createSeededRandom(seed = 1) {
    let state = seed >>> 0;

    return function random() {
        state = (1664525 * state + 1013904223) >>> 0;
        return state / 0x100000000;
    };
}

export function seededId(prefix, index) {
    return `${prefix}-${String(index).padStart(4, '0')}`;
}

export function seededText(random, words = 12) {
    const lexicon = [
        'lorem',
        'ipsum',
        'dolor',
        'sit',
        'amet',
        'consectetur',
        'adipiscing',
        'elit',
        'sed',
        'do',
        'eiusmod',
        'tempor',
        'incididunt',
        'ut',
        'labore',
        'et',
        'dolore',
        'magna',
        'aliqua',
    ];

    return Array.from({ length: words }, () => lexicon[Math.floor(random() * lexicon.length)]).join(' ');
}
