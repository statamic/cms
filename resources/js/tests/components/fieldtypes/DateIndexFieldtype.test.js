import { mount } from '@vue/test-utils';
import { test, expect, beforeEach } from 'vitest';
import DateIndexFieldtype from '@/components/fieldtypes/DateIndexFieldtype.vue';
import DateFormatter from '@/components/DateFormatter.js';

window.__ = (key) => key;

window.matchMedia = () => ({
    addEventListener: () => {},
});

const makeDateIndexField = (value = {}) => {
    return mount(DateIndexFieldtype, {
        props: {
            handle: 'date',
            value,
            values: {},
        },
    });
};

beforeEach(() => {
    process.env.TZ = 'UTC';
    DateFormatter.defaultLocale = 'en';
});

test.each([
    ['UTC', '12/25/2025'],
    ['America/New_York', '12/24/2025'],
])('date is localized to the users timezone (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        date: '2025-12-25T02:13:00Z',
        mode: 'single',
        format_has_time: true,
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['UTC', '12/25/2025'],
    ['America/New_York', '12/25/2025'],
])('date-only value does not shift across timezones (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        date: '2025-12-25',
        mode: 'single',
        format_has_time: false,
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['UTC', '12/25/2025 – 12/28/2025'],
    ['America/New_York', '12/25/2025 – 12/28/2025'],
])('date-only range does not shift across timezones (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        start: '2025-12-25',
        end: '2025-12-28',
        mode: 'range',
        format_has_time: false,
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['UTC', '12/25/2025, 2:13 AM'],
    ['America/New_York', '12/24/2025, 9:13 PM'],
])('date and time is localized to the users timezone (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        date: '2025-12-25T02:13:00Z',
        mode: 'single',
        time_enabled: true,
        format_has_time: true,
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['UTC', '12/25/2025 – 12/28/2025'],
    ['America/New_York', '12/24/2025 – 12/27/2025'],
])('date range is localized to the users timezone (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        start: '2025-12-25T02:13:00Z',
        end: '2025-12-28T03:59:00Z',
        mode: 'range',
        format_has_time: true,
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['en', '12/25/2025'],
    ['de', '25.12.2025'],
    ['fr', '25/12/2025'],
])('date is formatted to the users browser language (%s)', async (lang, expected) => {
    DateFormatter.defaultLocale = lang;

    const dateIndexField = makeDateIndexField({ date: '2025-12-25T13:29:00Z', format_has_time: true });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['en', '12/25/2025, 1:29 PM'],
    ['de', '25.12.2025, 13:29'],
    ['fr', '25/12/2025 13:29'],
])('date and time is formatted to the users browser language (%s)', async (lang, expected) => {
    DateFormatter.defaultLocale = lang;

    const dateIndexField = makeDateIndexField({
        date: '2025-12-25T13:29:00Z',
        time_enabled: true,
        format_has_time: true,
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['en', '12/25/2025 – 12/28/2025'],
    ['de', '25.12.2025 – 28.12.2025'],
    ['fr', '25/12/2025 – 28/12/2025'],
])('date range is formatted to the users browser language (%s)', async (lang, expected) => {
    DateFormatter.defaultLocale = lang;

    const dateIndexField = makeDateIndexField({
        start: '2025-12-25T02:13:00Z',
        end: '2025-12-28T03:59:00Z',
        mode: 'range',
        format_has_time: true,
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test('date-only format omits time and hides the timezone hover card', async () => {
    const dateIndexField = makeDateIndexField({
        date: '2025-12-25',
        mode: 'single',
        time_enabled: false,
        format_has_time: false,
    });

    expect(dateIndexField.vm.formatted).toBe('12/25/2025');
    expect(dateIndexField.vm.showTimezoneCard).toBe(false);
});

test('time-disabled but time-aware format shows date in value and exposes the timezone hover card', async () => {
    const dateIndexField = makeDateIndexField({
        date: '2025-12-25T02:13:00Z',
        mode: 'single',
        time_enabled: false,
        format_has_time: true,
    });

    expect(dateIndexField.vm.formatted).toBe('12/25/2025');
    expect(dateIndexField.vm.showTimezoneCard).toBe(true);
});

test('time-enabled value shows time in value and exposes the timezone hover card', async () => {
    const dateIndexField = makeDateIndexField({
        date: '2025-12-25T02:13:00Z',
        mode: 'single',
        time_enabled: true,
        format_has_time: true,
    });

    expect(dateIndexField.vm.formatted).toBe('12/25/2025, 2:13 AM');
    expect(dateIndexField.vm.showTimezoneCard).toBe(true);
});
