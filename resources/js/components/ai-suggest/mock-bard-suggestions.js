import { sentence, tighten, warmer } from './mock-suggestions.js';

// Prototype: pretend an AI rewrote a Bard field. Only its headings and paragraphs are looked at, as plain text, so bold, italics and links
// are lost once a suggestion is used. Each suggestion keeps the field's blocks in order: a block is either rewritten or dropped (null).

const textOf = (node) => (node.type === 'text' ? node.text : (node.content ?? []).map(textOf).join(''));
const wordCount = (text) => text.split(/\s+/).filter(Boolean).length;

export function blocksOf(value) {
    return (Array.isArray(value) ? value : [])
        .filter((node) => ['paragraph', 'heading'].includes(node.type))
        .map((node) => ({ type: node.type, level: node.attrs?.level, text: textOf(node) }))
        .filter((block) => block.text.trim());
}

// Back into Bard's content
export function toBard(blocks) {
    return blocks.filter(Boolean).map((block) =>
        block.type === 'heading'
            ? { type: 'heading', attrs: { level: block.level ?? 2 }, content: [{ type: 'text', text: block.text }] }
            : { type: 'paragraph', content: [{ type: 'text', text: block.text }] },
    );
}

// One line on what a suggestion does, so they can be told apart without reading them all: "Cuts 23 words · drops 2 paragraphs"
function summaryOf(original, blocks) {
    const before = original.reduce((total, block) => total + wordCount(block.text), 0);
    const after = blocks.filter(Boolean).reduce((total, block) => total + wordCount(block.text), 0);
    const dropped = blocks.filter((block) => block === null).length;
    const changed = blocks.filter((block, i) => block && block.text !== original[i].text).length;

    return [
        after < before ? `Cuts ${before - after} words` : after > before ? `Adds ${after - before} words` : 'Same length',
        dropped ? `drops ${dropped} ${dropped === 1 ? 'paragraph' : 'paragraphs'}` : `rewords ${changed} of ${original.length}`,
    ].join(' · ');
}

export function suggestForBard(value, label = null) {
    const original = blocksOf(value);
    const reword = (block, text) => ({ ...block, text });

    // Tighter: every paragraph without its filler, headings as they are
    const tighter = original.map((block) => (block.type === 'heading' ? block : reword(block, sentence(tighten(block.text)))));

    // Shorter: the same, keeping only each section's first paragraph
    let inSection = 0;
    const shorter = tighter.map((block) => {
        if (block.type === 'heading') return (inSection = 0), block;
        return ++inSection > 1 ? null : block;
    });

    // Warmer: the same, with a friendlier opening
    const first = original.findIndex((block) => block.type === 'paragraph');
    const warmerBlocks = tighter.map((block, i) => (i === first ? reword(block, sentence(warmer(original[i].text, true))) : block));

    return [
        { label: label ?? 'Tighter', blocks: tighter },
        { label: label ? `${label}, shorter` : 'Shorter', blocks: shorter },
        { label: label ? `${label}, warmer` : 'Warmer', blocks: warmerBlocks },
    ].map((suggestion) => ({ ...suggestion, summary: summaryOf(original, suggestion.blocks) }));
}

// Each of the field's blocks next to what the suggestion makes of it: after is null when it's dropped
export function changesOf(value, suggestion) {
    return blocksOf(value).map((block, i) => ({ ...block, index: i, before: block.text, after: suggestion.blocks[i]?.text ?? null, changed: suggestion.blocks[i]?.text !== block.text }));
}
