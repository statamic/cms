export default class DateFormatter {
    #date;
    #options;
    #locale = navigator.language;
    #presets = {
        datetime: {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
        },
        date: {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
        },
        time: {
            timeStyle: 'short',
        },
    };

    constructor(date, options) {
        this.#date = this.#normalizeDate(date);
        this.#options = this.#normalizeOptions(options);
    }

    date(value) {
        return new DateFormatter(value, this.#options);
    }

    options(options) {
        return new DateFormatter(this.#date, options);
    }

    toString() {
        try {
            return Intl.DateTimeFormat(this.locale, this.#options).format(this.#date);
        } catch (e) {
            return 'Invalid Date';
        }
    }

    static format(date, options) {
        return new DateFormatter(date, options).toString();
    }

    format(date, options) {
        return this.date(date).options(options).toString();
    }

    get locale() {
        return this.#locale;
    }

    #normalizeDate(date) {
        if (!date || date === 'now') return Date.now();

        if (date instanceof Date) return date;

        return new Date(date);
    }

    #normalizeOptions(options) {
        if (!options) options = 'datetime';

        if (typeof options === 'string') {
            if (!this.#presets[options]) throw new Error(`Invalid date format: ${options}`);

            return this.#presets[options];
        }

        return options;
    }
}
