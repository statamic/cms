import { beforeEach, describe, expect, test } from 'vitest';
import DateFormatter from '@statamic/components/DateFormatter.js';

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
