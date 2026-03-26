import { beforeEach, describe, expect, test } from 'vitest';
import NumberFormatter from '@/components/NumberFormatter.js';

beforeEach(() => {
    NumberFormatter.defaultLocale = 'en-us';
});

test('it can cast to string', () => {
    const formatter = new NumberFormatter();
    expect(`${formatter}`).toBe('0');
});

test('it can set up options before hand', () => {
    const formatted = new NumberFormatter()
        .options({
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
        .toString();

    expect(formatted).toBe('0.00');
});

test('it can pass the number separately', () => {
    const formatter = new NumberFormatter().number(99.5);
    expect(formatter.toString()).toBe('99.5');
});

test('it can statically format', () => {
    expect(NumberFormatter.format(0.25, 'percent')).toBe('25%');
    expect(NumberFormatter.format(1234.5, { minimumFractionDigits: 2 })).toBe('1,234.50');
});

test('it can temporarily format with locale using callback', () => {
    const formatter = new NumberFormatter();
    NumberFormatter.defaultLocale = 'en-us';

    const result = formatter.withLocale('de', (instance) => instance.format(1234.567, 'decimal'));

    expect(result).toBe('1.234,567');
    expect(NumberFormatter.defaultLocale).toBe('en-us');
});

test('it resets locale after withLocale callback throws', () => {
    const formatter = new NumberFormatter();
    NumberFormatter.defaultLocale = 'en-us';

    expect(() => formatter.withLocale('de', () => {
        throw new Error('boom');
    })).toThrow('boom');

    expect(NumberFormatter.defaultLocale).toBe('en-us');
});

test('it can format on the instance', () => {
    expect(new NumberFormatter().format(1234.5)).toBe('1,234.5');
    expect(new NumberFormatter().format(1234.5, { maximumFractionDigits: 0 })).toBe('1,235');
});

describe('numbers can be provided in various ways', () => {
    const n = 1234.5;
    const expectedFormat = '1,234.5';
    const expectedZero = '0';

    test.each([
        {
            name: 'constructor with number literal',
            value: () => new NumberFormatter(n),
            expected: expectedFormat,
        },
        {
            name: 'constructor with numeric string',
            value: () => new NumberFormatter('1234.5'),
            expected: expectedFormat,
        },
        {
            name: 'constructor with null',
            value: () => new NumberFormatter(null),
            expected: expectedZero,
        },
        {
            name: 'constructor with undefined',
            value: () => new NumberFormatter(undefined),
            expected: expectedZero,
        },
        {
            name: 'number with number literal',
            value: () => new NumberFormatter().number(n),
            expected: expectedFormat,
        },
        {
            name: 'number with numeric string',
            value: () => new NumberFormatter().number('1234.5'),
            expected: expectedFormat,
        },
        {
            name: 'number with null',
            value: () => new NumberFormatter().number(null),
            expected: expectedZero,
        },
        {
            name: 'format with number literal',
            value: () => new NumberFormatter().format(n),
            expected: expectedFormat,
        },
        {
            name: 'format with numeric string',
            value: () => new NumberFormatter().format('1234.5'),
            expected: expectedFormat,
        },
        {
            name: 'format with null',
            value: () => new NumberFormatter().format(null),
            expected: expectedZero,
        },
        {
            name: 'static format with number literal',
            value: () => NumberFormatter.format(n),
            expected: expectedFormat,
        },
        {
            name: 'static format with numeric string',
            value: () => NumberFormatter.format('1234.5'),
            expected: expectedFormat,
        },
        {
            name: 'static format with null',
            value: () => NumberFormatter.format(null),
            expected: expectedZero,
        },
    ])('by $name', ({ value, expected }) => {
        value = value();
        expect(`${value}`).toBe(expected);
    });
});

test.each([
    ['constructor with NaN', () => new NumberFormatter(NaN)],
    ['constructor with invalid string', () => new NumberFormatter('foo')],
    ['number() with NaN', () => new NumberFormatter().number(NaN)],
    ['number() with invalid string', () => new NumberFormatter().number('foo')],
    ['format() with NaN', () => new NumberFormatter().format(NaN)],
    ['format() with invalid string', () => new NumberFormatter().format('foo')],
    ['static format with NaN', () => NumberFormatter.format(NaN)],
    ['static format with invalid string', () => NumberFormatter.format('foo')],
])('it throws for invalid number: %s', (label, fn) => {
    expect(fn).toThrow('Invalid Number');
});

test('it can get the locale', () => {
    expect(new NumberFormatter().locale).toBe('en-us');
    NumberFormatter.defaultLocale = 'fr';
    expect(new NumberFormatter().locale).toBe('fr');
});

test('it can set the default locale via setDefaultLocale', () => {
    new NumberFormatter().setDefaultLocale('de');
    expect(NumberFormatter.defaultLocale).toBe('de');
    expect(new NumberFormatter().locale).toBe('de');
});

test.each([
    ['en', 'decimal', 1234.567, '1,234.567'],
    ['en', 'percent', 0.2534, '25%'],
    ['de', 'decimal', 1234.567, '1.234,567'],
    ['de', 'percent', 0.2534, '25\u00a0%'],
])('it has format presets (%s %s)', (locale, preset, number, expected) => {
    NumberFormatter.defaultLocale = locale;
    expect(new NumberFormatter(number, preset).toString()).toBe(expected);
});

test('an invalid preset throws an error', () => {
    expect(() => new NumberFormatter().options('foo')).toThrow('Invalid number format: foo');
});

describe('formatRange', () => {
    test('it can format a range with default options', () => {
        expect(new NumberFormatter().formatRange(1000, 5000)).toBe('1,000–5,000');
    });

    test('it can format a range with a preset', () => {
        expect(new NumberFormatter().formatRange(0.1, 0.85, 'percent')).toBe('10% – 85%');
    });

    test('it can format a range with custom options', () => {
        expect(new NumberFormatter().formatRange(100, 999.99, { style: 'currency', currency: 'USD' })).toBe('$100.00 – $999.99');
    });

    test('it uses the instance options when none are provided', () => {
        expect(new NumberFormatter(0, 'percent').formatRange(0.1, 0.5)).toBe('10% – 50%');
    });

    test('it can format a range with a different locale', () => {
        NumberFormatter.defaultLocale = 'de';
        expect(new NumberFormatter().formatRange(1000, 5000)).toBe('1.000–5.000');
    });

    test('it can statically format a range', () => {
        expect(NumberFormatter.formatRange(1000, 5000)).toBe('1,000–5,000');
        expect(NumberFormatter.formatRange(0.1, 0.85, 'percent')).toBe('10% – 85%');
    });

    test('it normalizes string inputs', () => {
        expect(new NumberFormatter().formatRange('1000', '5000')).toBe('1,000–5,000');
    });
});

describe('range via array syntax', () => {
    test('it formats a range when an array is provided to the constructor', () => {
        expect(new NumberFormatter([1, 2]).toString()).toBe('1–2');
    });

    test('it formats a range when an array is provided to number()', () => {
        expect(new NumberFormatter().number([1, 2]).toString()).toBe('1–2');
    });

    test('it can use a preset while formatting a range', () => {
        expect(new NumberFormatter([0.1, 0.2], 'percent').toString()).toBe('10% – 20%');
    });

    test('it preserves range when options() is chained', () => {
        expect(new NumberFormatter([1234.5, 1234.9]).options({ minimumFractionDigits: 2, maximumFractionDigits: 2 }).toString()).toBe('1,234.50–1,234.90');
    });

    test('it resets locale after withLocale callback throws', () => {
        const formatter = new NumberFormatter();
        NumberFormatter.defaultLocale = 'en-us';

        expect(() => formatter.withLocale('de', (instance) => {
            expect(instance.number([1234.567, 1234.9]).toString()).toBe('1.234,567–1.234,9');
            throw new Error('boom');
        })).toThrow('boom');

        expect(NumberFormatter.defaultLocale).toBe('en-us');
    });

    test('it throws for NaN range endpoints', () => {
        expect(() => new NumberFormatter([NaN, 2])).toThrow('Invalid Number');
        expect(() => new NumberFormatter([1, 'foo'])).toThrow('Invalid Number');
    });
});
