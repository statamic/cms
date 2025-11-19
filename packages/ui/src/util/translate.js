let translateFn = fallbackTranslate;

export function translate(key, replacements) {
    return translateFn(key, replacements);
}

export function setTranslateFn(fn) {
    translateFn = fn;
}

function fallbackTranslate(key, replacements) {
    let message = key;

    for (let replace in replacements) {
        message = message.split(':' + replace).join(replacements[replace]);
    }

    return message;
}
