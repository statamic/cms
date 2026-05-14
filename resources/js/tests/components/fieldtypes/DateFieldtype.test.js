import { mount } from '@vue/test-utils';
import { test, expect, vi, afterEach } from 'vitest';
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

const makeDateField = (props = {}) => {
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

test('configured timezone overrides browser timezone', () => {
    process.env.TZ = 'America/New_York';

    const dateField = makeDateField({
        value: '2025-12-25T02:23:00Z',
        meta: { timezone: 'Europe/London' },
    });

    expect(dateField.vm.datePickerValue.toString()).toBe('2025-12-25T02:23:00+00:00[Europe/London]');
});

test('displayTimezone falls back to browser local when timezone is auto', () => {
    process.env.TZ = 'America/New_York';

    const dateField = makeDateField({
        value: '2025-12-25T02:23:00Z',
        meta: { timezone: 'auto' },
    });

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

test.each([
    ['UTC'],
    ['America/New_York'],
    ['Australia/Sydney'],
])('date-only format is not affected by timezone (%s)', async (tz) => {
    process.env.TZ = tz;

    const dateField = makeDateField({
        value: '2025-12-25',
        meta: { formatHasTime: false },
    });

    const value = dateField.vm.datePickerValue;
    expect(value.year).toBe(2025);
    expect(value.month).toBe(12);
    expect(value.day).toBe(25);
    expect(value.timeZone).toBeUndefined();
});

test('date-only format formats date correctly', async () => {
    process.env.TZ = 'America/New_York';

    const dateField = makeDateField({
        value: '2025-12-25',
        meta: { formatHasTime: false },
    });

    const { CalendarDate } = await import('@internationalized/date');
    const formatted = dateField.vm.formatDateOnly(new CalendarDate(2025, 6, 5));

    expect(formatted).toBe('2025-06-05');
});

test.each([
    [{ start: { year: 2025, month: 12, day: 25 }, end: undefined }],
    [{ start: undefined, end: { year: 2025, month: 12, day: 31 } }],
])('partial range update is ignored', async (value) => {
    const dateField = makeDateField({
        meta: { formatHasTime: false },
        config: {
            mode: 'range',
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
        },
    });

    expect(() => dateField.vm.datePickerUpdated(value)).not.toThrow();
    expect(dateField.emitted('update:value')).toBeUndefined();
});

afterEach(() => vi.useRealTimers());

test('addDate uses displayTimezone for date-only format', () => {
    // 11pm Dec 24 in NY is already Dec 25 in London.
    process.env.TZ = 'America/New_York';
    vi.useFakeTimers();
    vi.setSystemTime(new Date('2025-12-25T04:00:00Z'));

    const dateField = makeDateField({
        meta: { formatHasTime: false, timezone: 'Europe/London' },
    });

    dateField.vm.addDate();

    expect(dateField.emitted('update:value')[0][0]).toBe('2025-12-25');
});

test('addDate uses midnight in displayTimezone when time is disabled', () => {
    // Without the displayTimezone anchor we'd get NY-midnight as UTC (Dec 24 05:00Z).
    process.env.TZ = 'America/New_York';
    vi.useFakeTimers();
    vi.setSystemTime(new Date('2025-12-25T04:00:00Z'));

    const dateField = makeDateField({
        meta: { formatHasTime: true, timezone: 'Europe/London' },
        config: {
            time_enabled: false,
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
        },
    });

    dateField.vm.addDate();

    expect(dateField.emitted('update:value')[0][0]).toBe('2025-12-25T00:00:00.000Z');
});

test('addDate preserves the current instant when time is enabled', () => {
    process.env.TZ = 'America/New_York';
    vi.useFakeTimers();
    vi.setSystemTime(new Date('2025-12-25T02:23:45.678Z'));

    const dateField = makeDateField({
        meta: { formatHasTime: true, timezone: 'Europe/London' },
        config: {
            time_enabled: true,
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
        },
    });

    dateField.vm.addDate();

    expect(dateField.emitted('update:value')[0][0]).toBe('2025-12-25T02:23:45.000Z');
});

test('date-only range format is not affected by timezone', async () => {
    process.env.TZ = 'America/New_York';

    const dateField = makeDateField({
        value: { start: '2025-12-25', end: '2025-12-31' },
        meta: { formatHasTime: false },
        config: {
            mode: 'range',
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
        },
    });

    const value = dateField.vm.datePickerValue;
    expect(value.start.year).toBe(2025);
    expect(value.start.month).toBe(12);
    expect(value.start.day).toBe(25);
    expect(value.end.year).toBe(2025);
    expect(value.end.month).toBe(12);
    expect(value.end.day).toBe(31);
});
