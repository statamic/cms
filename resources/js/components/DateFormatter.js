export default class DateFormatter {
    #date;
    #options;
    #locale = navigator.language;

    constructor(date, options) {
        this.#date = date;
        this.#options = options ?? {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
        };
    }

    of(value) {
        return this.date(value);
    }

    date(value) {
        return new DateFormatter(new Date(value), this.#options);
    }

    options(options) {
        return new DateFormatter(this.#date, options);
    }

    toString() {
        return Intl.DateTimeFormat(this.locale, this.#options).format(this.#date);
    }

    get locale() {
        return this.#locale;
    }
}
