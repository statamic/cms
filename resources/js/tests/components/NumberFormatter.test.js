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
            name: 'constructor with NaN',
            value: () => new NumberFormatter(NaN),
            expected: 'Invalid Number',
        },
        {
            name: 'constructor with invalid string',
            value: () => new NumberFormatter('foo'),
            expected: 'Invalid Number',
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
            name: 'number with NaN',
            value: () => new NumberFormatter().number(NaN),
            expected: 'Invalid Number',
        },
        {
            name: 'number with invalid string',
            value: () => new NumberFormatter().number('foo'),
            expected: 'Invalid Number',
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
            name: 'format with NaN',
            value: () => new NumberFormatter().format(NaN),
            expected: 'Invalid Number',
        },
        {
            name: 'format with invalid string',
            value: () => new NumberFormatter().format('foo'),
            expected: 'Invalid Number',
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
        {
            name: 'static format with NaN',
            value: () => NumberFormatter.format(NaN),
            expected: 'Invalid Number',
        },
        {
            name: 'static format with invalid string',
            value: () => NumberFormatter.format('foo'),
            expected: 'Invalid Number',
        },
    ])('by $name', ({ value, expected }) => {
        value = value();
        expect(`${value}`).toBe(expected);
    });
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
