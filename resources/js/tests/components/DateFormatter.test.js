import { beforeEach, describe, expect, test } from 'vitest';
import DateFormatter from '@/components/DateFormatter.js';

function setNavigatorLanguage(lang) {
    Object.defineProperty(navigator, 'language', {
        value: lang,
        writable: true,
    });
}

let originalDate;
function setMockDate(dateString) {
    originalDate = Date; // Store the original Date object
    global.Date = class extends Date {
        constructor(...args) {
            if (args.length) return super(...args);
            return new originalDate(dateString);
        }
        static now() {
            return new Date();
        }
    };
}

beforeEach(() => {
    process.env.TZ = 'UTC';
    setNavigatorLanguage('en-us');
    setMockDate('2021-12-25T12:13:14Z');
});

test('it can cast to string', () => {
    const formatter = new DateFormatter();
    expect(`${formatter}`).toBe('12/25/2021, 12:13 PM');
});

test('it can set up options before hand', () => {
    const formatted = new DateFormatter()
        .options({
            dateStyle: 'long',
            timeStyle: 'long',
        })
        .toString();

    expect(formatted).toBe('December 25, 2021 at 12:13:14 PM UTC');
});

test('it can pass the date separately', () => {
    const formatter = new DateFormatter().date('1995-03-13T22:45:19Z');
    expect(formatter.toString()).toBe('3/13/1995, 10:45 PM');
});

test('it can statically format', () => {
    expect(DateFormatter.format('1995-03-13T22:45:19Z')).toBe('3/13/1995, 10:45 PM');
    expect(DateFormatter.format('1995-03-13T22:45:19Z', { year: 'numeric' })).toBe('1995');
});

test('it can format on the instance', () => {
    expect(new DateFormatter().format('1995-03-13T22:45:19Z')).toBe('3/13/1995, 10:45 PM');
    expect(new DateFormatter().format('1995-03-13T22:45:19Z', { year: 'numeric' })).toBe('1995');
});

describe('dates can be provided in various ways', () => {
    const iso = '1995-03-13T22:45:19Z';
    const instance = new Date(iso);
    const expectedFormat = '3/13/1995, 10:45 PM';
    const expectedNowFormat = '12/25/2021, 12:13 PM';

    test.each([
        {
            name: 'constructor with iso8601',
            value: () => new DateFormatter(iso),
            expected: expectedFormat,
        },
        {
            name: 'constructor with instance',
            value: () => new DateFormatter(instance),
            expected: expectedFormat,
        },
        {
            name: 'constructor with timestamp',
            value: () => new DateFormatter(instance.getTime()),
            expected: expectedFormat,
        },
        {
            name: 'constructor with null',
            value: () => new DateFormatter(),
            expected: expectedNowFormat,
        },
        {
            name: 'constructor with now',
            value: () => new DateFormatter('now'),
            expected: expectedNowFormat,
        },
        {
            name: 'constructor with invalid date',
            value: () => new DateFormatter('foo'),
            expected: 'Invalid Date',
        },
        {
            name: 'date with iso8601',
            value: () => new DateFormatter().date(iso),
            expected: expectedFormat,
        },
        {
            name: 'date with instance',
            value: () => new DateFormatter().date(instance),
            expected: expectedFormat,
        },
        {
            name: 'date with timestamp',
            value: () => new DateFormatter().date(instance.getTime()),
            expected: expectedFormat,
        },
        {
            name: 'date with null',
            value: () => new DateFormatter().date(null),
            expected: expectedNowFormat,
        },
        {
            name: 'date with now',
            value: () => new DateFormatter().date('now'),
            expected: expectedNowFormat,
        },
        {
            name: 'date with invalid date',
            value: () => new DateFormatter().date('foo'),
            expected: 'Invalid Date',
        },
        {
            name: 'format with iso8601',
            value: () => new DateFormatter().format(iso),
            expected: expectedFormat,
        },
        {
            name: 'format with instance',
            value: () => new DateFormatter().format(instance),
            expected: expectedFormat,
        },
        {
            name: 'format with timestamp',
            value: () => new DateFormatter().format(instance.getTime()),
            expected: expectedFormat,
        },
        {
            name: 'format with null',
            value: () => new DateFormatter().format(null),
            expected: expectedNowFormat,
        },
        {
            name: 'format with invalid date',
            value: () => new DateFormatter().format('foo'),
            expected: 'Invalid Date',
        },
        {
            name: 'static format with iso8601',
            value: () => DateFormatter.format(iso),
            expected: expectedFormat,
        },
        {
            name: 'static format with instance',
            value: () => DateFormatter.format(instance),
            expected: expectedFormat,
        },
        {
            name: 'static format with timestamp',
            value: () => DateFormatter.format(instance.getTime()),
            expected: expectedFormat,
        },
        {
            name: 'static format with null',
            value: () => DateFormatter.format(null),
            expected: expectedNowFormat,
        },
        {
            name: 'static format with now',
            value: () => DateFormatter.format('now'),
            expected: expectedNowFormat,
        },
        {
            name: 'static format with invalid date',
            value: () => DateFormatter.format('foo'),
            expected: 'Invalid Date',
        },
    ])('by $name', ({ value, expected }) => {
        value = value();
        expect(`${value}`).toBe(expected);
    });
});

test('it can get the locale', () => {
    expect(new DateFormatter().locale).toBe('en-us');
    setNavigatorLanguage('fr');
    expect(new DateFormatter().locale).toBe('fr');
});

test.each([
    ['en', 'date', '12/25/2021'],
    ['en', 'time', '12:13 PM'],
    ['en', 'datetime', '12/25/2021, 12:13 PM'],
    ['de', 'date', '25.12.2021'],
    ['de', 'time', '12:13'],
    ['de', 'datetime', '25.12.2021, 12:13'],
])('it has format presets (%s %s)', (locale, preset, expected) => {
    setNavigatorLanguage(locale);
    expect(new DateFormatter().options(preset).toString()).toBe(expected);
});

test('an invalid preset throws an error', () => {
    expect(() => new DateFormatter().options('foo')).toThrow('Invalid date format: foo');
});

test.each([
    // All the different diffs of time. It defaults to 'year' specificity.
    ['now', 'en', { relative: true }, '2021-12-25T12:13:14Z', 'now'],
    ['seconds ago', 'en', { relative: true }, '2021-12-25T12:13:12Z', '2 seconds ago'],
    ['minute ago', 'en', { relative: true }, '2021-12-25T12:12:14Z', '1 minute ago'],
    ['minutes ago', 'en', { relative: true }, '2021-12-25T12:11:14Z', '2 minutes ago'],
    ['hour ago', 'en', { relative: true }, '2021-12-25T11:13:14Z', '1 hour ago'],
    ['hours ago', 'en', { relative: true }, '2021-12-25T10:13:14Z', '2 hours ago'],
    ['day ago', 'en', { relative: true }, '2021-12-24T12:13:14Z', 'yesterday'],
    ['days ago', 'en', { relative: true }, '2021-12-23T12:13:14Z', '2 days ago'],
    ['week ago', 'en', { relative: true }, '2021-12-18T12:13:14Z', 'last week'],
    ['weeks ago', 'en', { relative: true }, '2021-12-11T12:13:14Z', '2 weeks ago'],
    ['month ago', 'en', { relative: true }, '2021-11-25T12:13:14Z', 'last month'],
    ['months ago', 'en', { relative: true }, '2021-10-25T12:13:14Z', '2 months ago'],
    ['year ago', 'en', { relative: true }, '2020-12-25T12:13:14Z', 'last year'],
    ['years ago', 'en', { relative: true }, '2019-12-25T12:13:14Z', '2 years ago'],

    // Same in another locale
    ['de, now', 'de', { relative: true }, '2021-12-25T12:13:14Z', 'jetzt'],
    ['de, seconds ago', 'de', { relative: true }, '2021-12-25T12:13:12Z', 'vor 2 Sekunden'],
    ['de, minute ago', 'de', { relative: true }, '2021-12-25T12:12:14Z', 'vor 1 Minute'],
    ['de, minutes ago', 'de', { relative: true }, '2021-12-25T12:11:14Z', 'vor 2 Minuten'],
    ['de, hour ago', 'de', { relative: true }, '2021-12-25T11:13:14Z', 'vor 1 Stunde'],
    ['de, hours ago', 'de', { relative: true }, '2021-12-25T10:13:14Z', 'vor 2 Stunden'],
    ['de, day ago', 'de', { relative: true }, '2021-12-24T12:13:14Z', 'gestern'],
    ['de, days ago', 'de', { relative: true }, '2021-12-23T12:13:14Z', 'vorgestern'],
    ['de, week ago', 'de', { relative: true }, '2021-12-18T12:13:14Z', 'letzte Woche'],
    ['de, weeks ago', 'de', { relative: true }, '2021-12-11T12:13:14Z', 'vor 2 Wochen'],
    ['de, month ago', 'de', { relative: true }, '2021-11-25T12:13:14Z', 'letzten Monat'],
    ['de, months ago', 'de', { relative: true }, '2021-10-25T12:13:14Z', 'vor 2 Monaten'],
    ['de, year ago', 'de', { relative: true }, '2020-12-25T12:13:14Z', 'letztes Jahr'],
    ['de, years ago', 'de', { relative: true }, '2019-12-25T12:13:14Z', 'vor 2 Jahren'],

    // All different specificities
    ['years ago, spec year', 'en', { relative: 'year' }, '2019-12-25T12:13:14Z', '2 years ago'],
    ['years ago, spec month', 'en', { relative: 'month' }, '2019-12-25T12:13:14Z', '12/25/2019, 12:13 PM'],
    ['years ago, spec week', 'en', { relative: 'week' }, '2019-12-25T12:13:14Z', '12/25/2019, 12:13 PM'],
    ['years ago, spec day', 'en', { relative: 'day' }, '2019-12-25T12:13:14Z', '12/25/2019, 12:13 PM'],
    ['years ago, spec hour', 'en', { relative: 'hour' }, '2019-12-25T12:13:14Z', '12/25/2019, 12:13 PM'],
    ['years ago, spec minute', 'en', { relative: 'minute' }, '2019-12-25T12:13:14Z', '12/25/2019, 12:13 PM'],
    ['years ago, spec second', 'en', { relative: 'second' }, '2019-12-25T12:13:14Z', '12/25/2019, 12:13 PM'],
    ['months ago, spec year', 'en', { relative: 'year' }, '2021-10-25T12:13:14Z', '2 months ago'],
    ['months ago, spec month', 'en', { relative: 'month' }, '2021-10-25T12:13:14Z', '2 months ago'],
    ['months ago, spec week', 'en', { relative: 'week' }, '2021-10-25T12:13:14Z', '10/25/2021, 12:13 PM'],
    ['months ago, spec day', 'en', { relative: 'day' }, '2021-10-25T12:13:14Z', '10/25/2021, 12:13 PM'],
    ['months ago, spec hour', 'en', { relative: 'hour' }, '2021-10-25T12:13:14Z', '10/25/2021, 12:13 PM'],
    ['months ago, spec minute', 'en', { relative: 'minute' }, '2021-10-25T12:13:14Z', '10/25/2021, 12:13 PM'],
    ['months ago, spec second', 'en', { relative: 'second' }, '2021-10-25T12:13:14Z', '10/25/2021, 12:13 PM'],
    ['weeks ago, spec year', 'en', { relative: 'year' }, '2021-12-11T12:13:14Z', '2 weeks ago'],
    ['weeks ago, spec month', 'en', { relative: 'month' }, '2021-12-11T12:13:14Z', '2 weeks ago'],
    ['weeks ago, spec week', 'en', { relative: 'week' }, '2021-12-11T12:13:14Z', '2 weeks ago'],
    ['weeks ago, spec day', 'en', { relative: 'day' }, '2021-12-11T12:13:14Z', '12/11/2021, 12:13 PM'],
    ['weeks ago, spec hour', 'en', { relative: 'hour' }, '2021-12-11T12:13:14Z', '12/11/2021, 12:13 PM'],
    ['weeks ago, spec minute', 'en', { relative: 'minute' }, '2021-12-11T12:13:14Z', '12/11/2021, 12:13 PM'],
    ['weeks ago, spec second', 'en', { relative: 'second' }, '2021-12-11T12:13:14Z', '12/11/2021, 12:13 PM'],
    ['days ago, spec year', 'en', { relative: 'year' }, '2021-12-23T12:13:14Z', '2 days ago'],
    ['days ago, spec month', 'en', { relative: 'month' }, '2021-12-23T12:13:14Z', '2 days ago'],
    ['days ago, spec week', 'en', { relative: 'week' }, '2021-12-23T12:13:14Z', '2 days ago'],
    ['days ago, spec day', 'en', { relative: 'day' }, '2021-12-23T12:13:14Z', '2 days ago'],
    ['days ago, spec hour', 'en', { relative: 'hour' }, '2021-12-23T12:13:14Z', '12/23/2021, 12:13 PM'],
    ['days ago, spec minute', 'en', { relative: 'minute' }, '2021-12-23T12:13:14Z', '12/23/2021, 12:13 PM'],
    ['days ago, spec second', 'en', { relative: 'second' }, '2021-12-23T12:13:14Z', '12/23/2021, 12:13 PM'],
    ['hours ago, spec year', 'en', { relative: 'year' }, '2021-12-25T10:13:14Z', '2 hours ago'],
    ['hours ago, spec month', 'en', { relative: 'month' }, '2021-12-25T10:13:14Z', '2 hours ago'],
    ['hours ago, spec week', 'en', { relative: 'week' }, '2021-12-25T10:13:14Z', '2 hours ago'],
    ['hours ago, spec day', 'en', { relative: 'day' }, '2021-12-25T10:13:14Z', '2 hours ago'],
    ['hours ago, spec hour', 'en', { relative: 'hour' }, '2021-12-25T10:13:14Z', '2 hours ago'],
    ['hours ago, spec minute', 'en', { relative: 'minute' }, '2021-12-25T10:13:14Z', '12/25/2021, 10:13 AM'],
    ['hours ago, spec second', 'en', { relative: 'second' }, '2021-12-25T10:13:14Z', '12/25/2021, 10:13 AM'],
    ['minutes ago, spec year', 'en', { relative: 'year' }, '2021-12-25T12:11:14Z', '2 minutes ago'],
    ['minutes ago, spec month', 'en', { relative: 'month' }, '2021-12-25T12:11:14Z', '2 minutes ago'],
    ['minutes ago, spec week', 'en', { relative: 'week' }, '2021-12-25T12:11:14Z', '2 minutes ago'],
    ['minutes ago, spec day', 'en', { relative: 'day' }, '2021-12-25T12:11:14Z', '2 minutes ago'],
    ['minutes ago, spec hour', 'en', { relative: 'hour' }, '2021-12-25T12:11:14Z', '2 minutes ago'],
    ['minutes ago, spec minute', 'en', { relative: 'minute' }, '2021-12-25T12:11:14Z', '2 minutes ago'],
    ['minutes ago, spec second', 'en', { relative: 'second' }, '2021-12-25T12:11:14Z', '12/25/2021, 12:11 PM'],
    ['seconds ago, spec year', 'en', { relative: 'year' }, '2021-12-25T12:13:12Z', '2 seconds ago'],
    ['seconds ago, spec month', 'en', { relative: 'month' }, '2021-12-25T12:13:12Z', '2 seconds ago'],
    ['seconds ago, spec week', 'en', { relative: 'week' }, '2021-12-25T12:13:12Z', '2 seconds ago'],
    ['seconds ago, spec day', 'en', { relative: 'day' }, '2021-12-25T12:13:12Z', '2 seconds ago'],
    ['seconds ago, spec hour', 'en', { relative: 'hour' }, '2021-12-25T12:13:12Z', '2 seconds ago'],
    ['seconds ago, spec minute', 'en', { relative: 'minute' }, '2021-12-25T12:13:12Z', '2 seconds ago'],
    ['seconds ago, spec second', 'en', { relative: 'second' }, '2021-12-25T12:13:12Z', '2 seconds ago'],
    [
        'fallback of datetime preset',
        'en',
        { relative: 'second', fallback: 'datetime' },
        '2021-12-20T12:13:14Z',
        '12/20/2021, 12:13 PM',
    ],
    ['fallback of time preset', 'en', { relative: 'second', fallback: 'time' }, '2021-12-20T12:13:14Z', '12:13 PM'],
    [
        'fallback of options',
        'en',
        { relative: 'second', fallback: { dateStyle: 'long', timeStyle: 'long' } },
        '2021-12-20T12:13:14Z',
        'December 20, 2021 at 12:13:14 PM UTC',
    ],
])('it can use relative format (%s)', (label, locale, options, date, expected) => {
    setNavigatorLanguage(locale);
    expect(new DateFormatter().format(date, options)).toBe(expected);
});
