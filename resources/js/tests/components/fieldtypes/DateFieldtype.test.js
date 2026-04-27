import { mount } from '@vue/test-utils';
import { test, expect } from 'vitest';
import DateFieldtype from '@/components/fieldtypes/DateFieldtype.vue';
import DateFormatter from '@/components/DateFormatter.js';
import { containerContextKey } from '@ui/Publish/Container.vue';

window.__ = (key) => key;

window.matchMedia = () => ({
    addEventListener: () => {},
});

window.Statamic = {
    get $date() {
        return new DateFormatter();
    },
};

const makeDateField = (props = {}, configOverrides = {}) => {
    return mount(DateFieldtype, {
        shallow: true,
        props: {
            handle: 'date',
            config: {
                earliest_date: { date: null, time: null },
                latest_date: { date: null, time: null },
            },
            ...props,
        },
        global: {
            provide: {
                [containerContextKey]: {
                    withoutDirtying: (callback) => callback(),
                }
            },
            mocks: {
                $config: {
                    get: (key) => {
                        if (key === 'locale') {
                            return 'en';
                        }
                        if (key === 'cpDateTimezone') {
                            return configOverrides.cpDateTimezone ?? 'auto';
                        }
                    },
                },
                $events: {
                    $on: () => {},
                },
            },
        },
    });
};

test.each([
    // ['UTC', '2025-12-25T02:23:00+00:00[UTC]'],
    ['America/New_York', '2025-12-24T21:23:00-05:00[America/New_York]'],
])('date and time is localized to the users timezone (%s)', async (tz, expectedDate) => {
    process.env.TZ = tz;

    const dateField = makeDateField({
        value: '2025-12-25T02:23:00Z',
    });

    expect(dateField.vm.datePickerValue.toString()).toBe(expectedDate);
});

test.each([
    // ['UTC', '2025-12-25T02:15:00+00:00[UTC]'],
    ['America/New_York', '2025-12-24T21:15:00-05:00[America/New_York]'],
])('local time is updated when value prop is updated (%s)', async (tz, expectedDate) => {
    process.env.TZ = tz;

    const dateField = makeDateField({
        value: '1984-01-01T15:00:00Z',
    });

    await dateField.setProps({ value: '2025-12-25T02:15:00Z' });

    expect(dateField.vm.datePickerValue.toString()).toBe(expectedDate);
});

test('datePickerValue returns null when value is "now"', () => {
    const dateField = makeDateField({ value: 'now' });

    expect(dateField.vm.datePickerValue).toBe(null);
});

test('per-field timezone overrides browser timezone', () => {
    process.env.TZ = 'America/New_York';

    const dateField = makeDateField({
        value: '2025-12-25T02:23:00Z',
        meta: { timezone: 'Europe/London' },
    });

    expect(dateField.vm.datePickerValue.toString()).toBe('2025-12-25T02:23:00+00:00[Europe/London]');
});

test('cp default timezone is used when no per-field timezone is set', () => {
    process.env.TZ = 'America/New_York';

    const dateField = makeDateField(
        { value: '2025-12-25T02:23:00Z' },
        { cpDateTimezone: 'Europe/London' },
    );

    expect(dateField.vm.datePickerValue.toString()).toBe('2025-12-25T02:23:00+00:00[Europe/London]');
});

test('per-field timezone takes precedence over cp default timezone', () => {
    process.env.TZ = 'America/New_York';

    const dateField = makeDateField(
        {
            value: '2025-12-25T02:23:00Z',
            meta: { timezone: 'Australia/Sydney' },
        },
        { cpDateTimezone: 'Europe/London' },
    );

    expect(dateField.vm.datePickerValue.toString()).toBe('2025-12-25T13:23:00+11:00[Australia/Sydney]');
});

test('displayTimezone falls back to browser local when cp default is auto', () => {
    process.env.TZ = 'America/New_York';

    const dateField = makeDateField(
        { value: '2025-12-25T02:23:00Z' },
        { cpDateTimezone: 'auto' },
    );

    expect(dateField.vm.displayTimezone).toBe('America/New_York');
});

test('range dates use configured timezone', () => {
    process.env.TZ = 'America/New_York';

    const dateField = makeDateField({
        value: { start: '2025-12-25T02:23:00Z', end: '2025-12-26T02:23:00Z' },
        meta: { timezone: 'Europe/London' },
        config: {
            mode: 'range',
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
        },
    });

    const value = dateField.vm.datePickerValue;
    expect(value.start.toString()).toBe('2025-12-25T02:23:00+00:00[Europe/London]');
    expect(value.end.toString()).toBe('2025-12-26T02:23:00+00:00[Europe/London]');
});
