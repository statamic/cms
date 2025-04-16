export default class DateFormatter {
    #date;
    #options;
    #locale = navigator.language;
    static presets = {
        datetime: {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            formatMatcher: 'basic',
        },
        date: {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
        },
        time: {
            timeStyle: 'short',
        },
        relative: {
            numeric: 'auto',
            style: 'long',
            specificity: 'day',
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
            if (this.#options === 'relative' || (this.#options.specificity && this.#options.numeric === 'auto')) {
                return this.#formatRelative();
            }
            return Intl.DateTimeFormat(this.locale, this.#options).format(this.#date);
        } catch (e) {
            return 'Invalid Date';
        }
    }

    #formatRelative() {
        const now = new Date();
        const diff = now - this.#date;
        const seconds = Math.abs(Math.floor(diff / 1000));
        const minutes = Math.abs(Math.floor(seconds / 60));
        const hours = Math.abs(Math.floor(minutes / 60));
        const days = Math.abs(Math.floor(hours / 24));
        const weeks = Math.abs(Math.floor(days / 7));
        const months = Math.abs(Math.floor(days / 30));
        const years = Math.abs(Math.floor(days / 365));

        const specificity = this.#options.specificity || DateFormatter.presets.relative.specificity;
        const rtf = new Intl.RelativeTimeFormat(this.locale, DateFormatter.presets.relative);

        // Always show seconds if less than a minute
        if (seconds < 60) return rtf.format(-Math.sign(diff), 'second');

        // Show minutes if less than an hour and specificity allows
        if (minutes < 60 && ['minute', 'hour', 'day', 'week', 'month', 'year'].includes(specificity)) {
            return rtf.format(-Math.sign(diff) * minutes, 'minute');
        }

        // Show hours if less than a day and specificity allows
        if (hours < 24 && ['hour', 'day', 'week', 'month', 'year'].includes(specificity)) {
            return rtf.format(-Math.sign(diff) * hours, 'hour');
        }

        // Show days if less than a week and specificity allows
        if (days < 7 && ['day', 'week', 'month', 'year'].includes(specificity)) {
            return rtf.format(-Math.sign(diff) * days, 'day');
        }

        // Show weeks if less than a month and specificity allows
        if (weeks < 4 && ['week', 'month', 'year'].includes(specificity)) {
            return rtf.format(-Math.sign(diff) * weeks, 'week');
        }

        // Show months if less than a year and specificity allows
        if (months < 12 && ['month', 'year'].includes(specificity)) {
            return rtf.format(-Math.sign(diff) * months, 'month');
        }

        // Show years if specificity allows
        if (specificity === 'year') {
            return rtf.format(-Math.sign(diff) * years, 'year');
        }

        // Custom format for datetime without comma
        const date = new Intl.DateTimeFormat(this.locale, {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
        }).format(this.#date);

        const time = new Intl.DateTimeFormat(this.locale, {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        }).format(this.#date);

        return `${date} ${time}`;
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
            if (!DateFormatter.presets[options]) throw new Error(`Invalid date format: ${options}`);

            return DateFormatter.presets[options];
        }

        return options;
    }
}
