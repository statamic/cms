import fontWidthMap from './TruncateTextCharacterMap.js';

export default function truncateOnResize (targetElement, originalText, { ellipsisSymbol = "..." } = {}) {
    if (!targetElement.offsetParent || !originalText) {
        return () => { };
    }

    const observer = new ResizeObserver(() => {
        targetElement.textContent = truncateText(targetElement, originalText, { ellipsisSymbol });
    });

    observer.observe(targetElement.offsetParent);

    return () => observer.disconnect();
};

function truncateText (targetElement, originalText, { ellipsisSymbol = "..." } = {}) {
    const { fontSize, fontFamily } = getElementProperties(targetElement);

    const availableWidth = getAvailableWidth(targetElement);
    const maxTextWidth = getStringWidth(originalText, fontSize, fontFamily);
    if (maxTextWidth <= availableWidth) {
        return originalText;
    }

    const middleEllipsisWidth = getStringWidth(ellipsisSymbol, fontSize, fontFamily);
    const originalTextLength = originalText.length;

    let remainingWidth = availableWidth - middleEllipsisWidth;
    let firstHalf = "";
    let secondHalf = "";

    for (let i = 0; i < Math.floor(originalTextLength / 2); i++) {
        remainingWidth -= getCharacterWidth(originalText[i], fontFamily, fontSize);
        if (remainingWidth < 0) {
            break;
        }

        firstHalf += originalText[i];
        remainingWidth -= getCharacterWidth(originalText[originalTextLength - i - 1], fontFamily, fontSize);
        if (remainingWidth < 0) {
            break;
        }

        secondHalf = originalText[originalTextLength - i - 1] + secondHalf;
    }

    return firstHalf + ellipsisSymbol + secondHalf;
};

function getCharacterWidth (character, fontFamily, fontSize = 16) {
    const characterWidthMap = fontWidthMap[fontFamily] ?? {};

    // If character is not present in widthMap, return width of 'W' character (widest character)
    const characterWidth = characterWidthMap[character] ?? characterWidthMap.W ?? fontSize;

    return characterWidth * (fontSize / 16); // scale the width according to fontSize
};

function getStringWidth (originalText, fontSize, fontFamily) {
    let width = 0;
    for (const c of originalText) {
        width += getCharacterWidth(c, fontFamily, fontSize);
    }
    return width;
};

function getElementProperties (targetElement) {
    const style = window.getComputedStyle(targetElement);
    return {
        fontSize: Number.parseFloat(style.fontSize),
        fontFamily: style.fontFamily.split(",")[0],
        width: Number.parseFloat(style.width),
    };
};

function getSiblingWidth (targetElement) {
    if (!targetElement.parentNode) {
        return 0;
    }

    let width = 0;
    for (const child of targetElement.parentNode.children) {
        if (child !== targetElement) {
            width += getElementProperties(child).width;
        }
    }

    return width;
};

function getAvailableWidth (targetElement) {
    const offsetParentElement = targetElement.offsetParent;
    if (!offsetParentElement) {
        return 0;
    }

    let takenWidth = 0;
    let tempElement = targetElement;
    while (tempElement && tempElement !== offsetParentElement) {
        takenWidth += getSiblingWidth(tempElement);
        tempElement = tempElement.parentElement;
    }

    return getElementProperties(offsetParentElement).width - takenWidth;
};
