import { getLocale, getDefaultLocale, setDefaultLocale } from './FormattingLocale.js';

export default class NumberFormatter {
    #number;
    #options;
    #presets = {
        decimal: {
            style: 'decimal',
        },
        percent: {
            style: 'percent',
        },
    };

    constructor(number, options) {
        this.#number = this.#normalizeNumber(number);
        this.#options = this.#normalizeOptions(options);
    }

    number(value) {
        return new NumberFormatter(value, this.#options);
    }

    options(options) {
        return new NumberFormatter(this.#number, options);
    }

    toString() {
        const n = this.#number;

        if (typeof n !== 'number' || Number.isNaN(n)) {
            return 'Invalid Number';
        }

        try {
            return Intl.NumberFormat(this.locale, this.#options).format(n);
        } catch (e) {
            return 'Invalid Number';
        }
    }

    static format(number, options) {
        return new NumberFormatter(number, options).toString();
    }

    format(number, options) {
        return this.number(number).options(options).toString();
    }

    static get defaultLocale() {
        return getDefaultLocale();
    }

    static set defaultLocale(locale) {
        setDefaultLocale(locale);
    }

    withLocale(locale, callback) {
        const previousLocale = getDefaultLocale();
        setDefaultLocale(locale);

        try {
            return callback(this);
        } finally {
            setDefaultLocale(previousLocale);
        }
    }

    setDefaultLocale(locale) {
        setDefaultLocale(locale);
    }

    get locale() {
        return getLocale();
    }

    #normalizeNumber(number) {
        if (number === null || number === undefined) return 0;

        if (typeof number === 'string') return Number(number);

        if (typeof number === 'number') return number;

        return Number(number);
    }

    #normalizeOptions(options) {
        if (!options) options = 'decimal';

        if (typeof options === 'string') {
            if (!this.#presets[options]) throw new Error(`Invalid number format: ${options}`);

            return this.#presets[options];
        }

        return options;
    }
}
