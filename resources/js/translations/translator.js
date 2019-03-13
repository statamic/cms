import selectPluralMessage from './message-selector';

/**
 * Get a translated string
 *
 * @param {String} key The translation key
 * @param {Object} replacements A key/value set of string replacements
 * @return String
 */
export const translate = function (key, replacements) {
    let message = getLine(key);

    for (let replace in replacements) {
        message = message.split(':' + replace).join(replacements[replace]);
    }

    return message;
};

/**
 * Get a translated string determined by plurality
 *
 * @param {String} key The translation key
 * @param {Integer|Array} count The number of items to determine the plurality. Can also just be an array.
 * @param {Object} replacements A key/value set of string replacements
 * @return String
 */
export const translateChoice = function (key, count, replacements) {
    replacements = typeof replacements !== 'undefined' ? replacements : {};

    // Add the count to the list of replacements. Allow users to pass an array to
    // be counted, which is a nicer syntax supported by Laravel's translator.
    count = Array.isArray(count) ? count.length : count;
    replacements.count = count;

    // Get the full translation. It will include all the piped plural versions, but with all replacements done.
    let message = translate(key, replacements);

    return selectPluralMessage(message, count, Statamic.$config.get('translationLocale'));
};

/**
 * Get a line from the supplied translation files
 * @param {String} key
 */
const getLine = function (key) {
    const translations = Statamic.$config.get('translations');

    return translations[`*.${key}`]
        || translations[key]
        || translations[`statamic::${key}`]
        || translations[`statamic::messages.${key}`]
        || key;
}
