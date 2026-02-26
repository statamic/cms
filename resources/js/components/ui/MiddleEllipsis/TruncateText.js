import characterWidths from './TruncateTextCharacterMap.js';

const BASE_FONT_SIZE = 16;

/**
 * Observes the parent element for resize events and truncates the text
 * with a middle ellipsis when it overflows.
 *
 * Returns a cleanup function to disconnect the observer.
 */
export default function truncateOnResize(element, text, { ellipsis = '...' } = {}) {
    if (!element.offsetParent || !text) {
        return () => {};
    }

    const observer = new ResizeObserver(() => {
        element.textContent = middleTruncate(element, text, ellipsis);
    });

    observer.observe(element.offsetParent);

    return () => observer.disconnect();
}

/**
 * Truncates text from the middle when it's too wide for the element,
 * preserving the start and end of the string for readability.
 */
function middleTruncate(element, text, ellipsis) {
    const style = window.getComputedStyle(element);
    const fontSize = Number.parseFloat(style.fontSize);
    const fontFamily = normalizeFontFamily(style.fontFamily.split(',')[0]);

    const availableWidth = measureAvailableWidth(element);

    if (measureTextWidth(text, fontFamily, fontSize) <= availableWidth) {
        return text;
    }

    let remainingWidth = availableWidth - measureTextWidth(ellipsis, fontFamily, fontSize);
    let start = '';
    let end = '';

    // Build the truncated string from both ends, one character at a time,
    // until we run out of space.
    for (let i = 0; i < Math.floor(text.length / 2); i++) {
        const startChar = text[i];
        remainingWidth -= measureCharWidth(startChar, fontFamily, fontSize);
        if (remainingWidth < 0) break;
        start += startChar;

        const endChar = text[text.length - i - 1];
        remainingWidth -= measureCharWidth(endChar, fontFamily, fontSize);
        if (remainingWidth < 0) break;
        end = endChar + end;
    }

    return start + ellipsis + end;
}

// -- Text width estimation ------------------------------------------------
// Uses a precomputed character width map (at 16px base size) to estimate
// text width without triggering DOM layout. Unmapped characters fall back
// to 'W' width (the widest common character) for a safe overestimate.

function measureCharWidth(char, fontFamily, fontSize) {
    const widths = characterWidths[fontFamily] ?? {};
    const baseWidth = widths[char] ?? widths.W ?? BASE_FONT_SIZE;
    return baseWidth * (fontSize / BASE_FONT_SIZE);
}

function normalizeFontFamily(fontFamily) {
    return fontFamily.replaceAll('"', '').replaceAll("'", '').trim().toLowerCase();
}

function measureTextWidth(text, fontFamily, fontSize) {
    let width = 0;
    for (const char of text) {
        width += measureCharWidth(char, fontFamily, fontSize);
    }
    return width;
}

function measureAvailableWidth(element) {
    const parent = element.parentElement;
    if (!parent) return 0;

    return Number.parseFloat(window.getComputedStyle(parent).width);
}
