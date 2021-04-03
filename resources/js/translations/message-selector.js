import { anyIntervalRegexp, testInterval } from './interval';

export default function choose(message, number, locale) {
    // Separate the plural from the singular, if any
    let messageParts = message.split('|');

    // Get the explicit rules, If any
    let explicitRules = [];

    for (let i = 0; i < messageParts.length; i++) {
        messageParts[i] = messageParts[i].trim();

        if (anyIntervalRegexp.test(messageParts[i])) {
            let messageSpaceSplit = messageParts[i].split(/\s/);
            explicitRules.push(messageSpaceSplit.shift());
            messageParts[i] = messageSpaceSplit.join(' ');
        }
    }

    // Check if there's only one message
    if (messageParts.length === 1) {
        // Nothing to do here
        return message;
    }

    // Check the explicit rules
    for (let j = 0; j < explicitRules.length; j++) {
        if (testInterval(number, explicitRules[j])) {
            return messageParts[j];
        }
    }

    const pluralForm = getPluralForm(number, locale);

    return messageParts[pluralForm];
}

const getPluralForm = function(count, locale) {
    // For regional locales with dashes, we just need the main part of the locale
    // e.g. For de_CH we just want de.
    if (locale.includes('_')) {
        locale = locale.substr(0, locale.indexOf('_'));
    }

    switch (locale) {
        case 'az':
        case 'bo':
        case 'dz':
        case 'id':
        case 'ja':
        case 'jv':
        case 'ka':
        case 'km':
        case 'kn':
        case 'ko':
        case 'ms':
        case 'th':
        case 'tr':
        case 'vi':
        case 'zh':
            return 0;

        case 'af':
        case 'bn':
        case 'bg':
        case 'ca':
        case 'da':
        case 'de':
        case 'el':
        case 'en':
        case 'eo':
        case 'es':
        case 'et':
        case 'eu':
        case 'fa':
        case 'fi':
        case 'fo':
        case 'fur':
        case 'fy':
        case 'gl':
        case 'gu':
        case 'ha':
        case 'he':
        case 'hu':
        case 'is':
        case 'it':
        case 'ku':
        case 'lb':
        case 'ml':
        case 'mn':
        case 'mr':
        case 'nah':
        case 'nb':
        case 'ne':
        case 'nl':
        case 'nn':
        case 'no':
        case 'om':
        case 'or':
        case 'pa':
        case 'pap':
        case 'ps':
        case 'pt':
        case 'so':
        case 'sq':
        case 'sv':
        case 'sw':
        case 'ta':
        case 'te':
        case 'tk':
        case 'ur':
        case 'zu':
            return (count == 1)
                ? 0
                : 1;

        case 'am':
        case 'bh':
        case 'fil':
        case 'fr':
        case 'gun':
        case 'hi':
        case 'hy':
        case 'ln':
        case 'mg':
        case 'nso':
        case 'xbr':
        case 'ti':
        case 'wa':
            return ((count === 0) || (count === 1))
                ? 0
                : 1;

        case 'be':
        case 'bs':
        case 'hr':
        case 'ru':
        case 'sr':
        case 'uk':
            return ((count % 10 == 1) && (count % 100 != 11))
                ? 0
                : (((count % 10 >= 2) && (count % 10 <= 4) && ((count % 100 < 10) || (count % 100 >= 20)))
                    ? 1
                    : 2);

        case 'cs':
        case 'sk':
            return (count == 1)
                ? 0
                : (((count >= 2) && (count <= 4))
                    ? 1
                    : 2);

        case 'ga':
            return (count == 1)
                ? 0
                : ((count == 2)
                    ? 1
                    : 2);

        case 'lt':
            return ((count % 10 == 1) && (count % 100 != 11))
                ? 0
                : (((count % 10 >= 2) && ((count % 100 < 10) || (count % 100 >= 20)))
                    ? 1
                    : 2);

        case 'sl':
            return (count % 100 == 1)
                ? 0
                : ((count % 100 == 2)
                    ? 1
                    : (((count % 100 == 3) || (count % 100 == 4))
                        ? 2
                        : 3));

        case 'mk':
            return (count % 10 == 1)
                ? 0
                : 1;

        case 'mt':
            return (count == 1)
                ? 0
                : (((count === 0) || ((count % 100 > 1) && (count % 100 < 11)))
                    ? 1
                    : (((count % 100 > 10) && (count % 100 < 20))
                        ? 2
                        : 3));

        case 'lv':
            return (count === 0)
                ? 0
                : (((count % 10 == 1) && (count % 100 != 11))
                    ? 1
                    : 2);

        case 'pl':
            return (count == 1)
                ? 0
                : (((count % 10 >= 2) && (count % 10 <= 4) && ((count % 100 < 12) || (count % 100 > 14)))
                    ? 1
                    : 2);

        case 'cy':
            return (count == 1)
                ? 0
                : ((count == 2)
                    ? 1
                    : (((count == 8) || (count == 11))
                        ? 2
                        : 3));

        case 'ro':
            return (count == 1)
                ? 0
                : (((count === 0) || ((count % 100 > 0) && (count % 100 < 20)))
                    ? 1
                    : 2);

        case 'ar':
            return (count === 0)
                ? 0
                : ((count == 1)
                    ? 1
                    : ((count == 2)
                        ? 2
                        : (((count % 100 >= 3) && (count % 100 <= 10))
                            ? 3
                            : (((count % 100 >= 11) && (count % 100 <= 99))
                                ? 4
                                : 5))));

        default:
            return 0;
    }
};
