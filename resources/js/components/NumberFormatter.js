import { getLocale, getDefaultLocale, setDefaultLocale } from './FormattingLocale.js';

export default class NumberFormatter {
    #number;
    #rangeEnd;
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
        if (Array.isArray(number)) {
            this.#number = this.#normalizeNumber(number[0]);
            this.#rangeEnd = this.#normalizeNumber(number[1]);
        } else {
            this.#number = this.#normalizeNumber(number);
        }

        this.#options = this.#normalizeOptions(options);
    }

    number(value) {
        return new NumberFormatter(value, this.#options);
    }

    options(options) {
        const value = this.#rangeEnd !== undefined ? [this.#number, this.#rangeEnd] : this.#number;

        return new NumberFormatter(value, options);
    }

    toString() {
        try {
            if (this.#rangeEnd !== undefined) {
                return Intl.NumberFormat(this.locale, this.#options).formatRange(this.#number, this.#rangeEnd);
            }

            return Intl.NumberFormat(this.locale, this.#options).format(this.#number);
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

    formatRange(start, end, options) {
        return this.number([start, end]).options(options ?? this.#options).toString();
    }

    static formatRange(start, end, options) {
        return new NumberFormatter([start, end], options).toString();
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

        const n = Number(number);

        if (Number.isNaN(n)) throw new Error('Invalid Number');

        return n;
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
