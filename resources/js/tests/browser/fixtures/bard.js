import { createSeededRandom, seededId, seededText } from './seeded.js';

export const BARD_PROFILES = {
    small: { paragraphs: 5, sets: 2, nestingDepth: 0, wordsPerParagraph: 20 },
    medium: { paragraphs: 25, sets: 10, nestingDepth: 1, wordsPerParagraph: 40 },
    pathological: { paragraphs: 80, sets: 40, nestingDepth: 2, wordsPerParagraph: 60 },
};

function makeParagraph(random, words) {
    return {
        type: 'paragraph',
        content: [{ type: 'text', text: seededText(random, words) }],
    };
}

function makeNestedBardValue(random, { paragraphs, wordsPerParagraph, nestingDepth }) {
    const content = [];

    for (let i = 0; i < paragraphs; i++) {
        content.push(makeParagraph(random, wordsPerParagraph));
    }

    if (nestingDepth > 0) {
        content.push(
            makeSetNode(random, {
                index: 0,
                nestingDepth: nestingDepth - 1,
                wordsPerParagraph,
            }),
        );
    }

    return content;
}

function makeSetNode(random, { index, nestingDepth, wordsPerParagraph }) {
    const id = seededId('set', index);
    const nestedParagraphs = Math.max(2, Math.floor(wordsPerParagraph / 10));

    return {
        type: 'set',
        attrs: {
            id,
            enabled: true,
            values: {
                type: 'page_builder',
                title: seededText(random, 4),
                body: makeNestedBardValue(random, {
                    paragraphs: nestedParagraphs,
                    wordsPerParagraph: Math.max(8, Math.floor(wordsPerParagraph / 2)),
                    nestingDepth,
                }),
            },
        },
    };
}

/**
 * Build a Bard ProseMirror JSON content array.
 */
export function makeBardValue(options = {}) {
    const {
        paragraphs = 5,
        sets = 0,
        nestingDepth = 0,
        wordsPerParagraph = 20,
        seed = 1,
    } = options;

    const random = createSeededRandom(seed);
    const content = [];
    let setIndex = 0;

    for (let i = 0; i < paragraphs; i++) {
        content.push(makeParagraph(random, wordsPerParagraph));

        // Interleave sets through the document.
        if (sets > 0 && (i + 1) % Math.max(1, Math.floor(paragraphs / sets)) === 0 && setIndex < sets) {
            content.push(
                makeSetNode(random, {
                    index: setIndex++,
                    nestingDepth,
                    wordsPerParagraph,
                }),
            );
        }
    }

    while (setIndex < sets) {
        content.push(
            makeSetNode(random, {
                index: setIndex++,
                nestingDepth,
                wordsPerParagraph,
            }),
        );
    }

    return content;
}

export function makeBardValueFromProfile(profile = 'small', overrides = {}) {
    const preset = BARD_PROFILES[profile] || BARD_PROFILES.small;

    return makeBardValue({ ...preset, ...overrides });
}

export function makeBardConfig({ withSets = true } = {}) {
    const sets = withSets
        ? [
              {
                  handle: 'main',
                  display: 'Main',
                  sets: [
                      {
                          handle: 'page_builder',
                          display: 'Page Builder',
                          fields: [
                              { handle: 'title', type: 'text', display: 'Title' },
                              {
                                  handle: 'body',
                                  type: 'bard',
                                  display: 'Body',
                                  buttons: ['bold', 'italic'],
                                  sets: [],
                              },
                          ],
                      },
                  ],
              },
          ]
        : [];

    return {
        display: 'Content',
        type: 'bard',
        buttons: ['bold', 'italic', 'h2', 'h3', 'unorderedlist', 'orderedlist'],
        toolbar_mode: 'fixed',
        container: 'assets',
        save_html: false,
        inline: false,
        enable_input_rules: true,
        enable_paste_rules: true,
        remove_empty_nodes: false,
        previews: true,
        sets,
    };
}

export function makeBardMeta(value = []) {
    const existing = {};

    for (const node of value) {
        if (node.type !== 'set') continue;

        existing[node.attrs.id] = {
            title: {},
            body: { existing: [], defaults: {}, new: {}, collapsed: [], flatten: false },
        };
    }

    return {
        existing,
        defaults: {
            page_builder: { title: null, body: [] },
        },
        new: {
            page_builder: {
                title: {},
                body: { existing: [], defaults: {}, new: {}, collapsed: [], flatten: false },
            },
        },
        collapsed: [],
        flatten: false,
    };
}
