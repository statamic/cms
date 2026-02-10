import { mount } from '@vue/test-utils';
import { test, expect, beforeEach } from 'vitest';
import DateIndexFieldtype from '@/components/fieldtypes/DateIndexFieldtype.vue';

window.__ = (key) => key;

window.matchMedia = () => ({
    addEventListener: () => {},
});

function setNavigatorLanguage(lang) {
    Object.defineProperty(navigator, 'language', {
        value: lang,
        writable: true,
    });
}

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
});

test.each([
    ['UTC', '12/25/2025'],
    ['America/New_York', '12/24/2025'],
])('date is localized to the users timezone (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        date: '2025-12-25',
        time: '02:13',
        mode: 'single',
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
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['en', '12/25/2025'],
    ['de', '25.12.2025'],
    ['fr', '25/12/2025'],
])('date is formatted to the users browser language (%s)', async (lang, expected) => {
    setNavigatorLanguage(lang);

    const dateIndexField = makeDateIndexField({ date: '2025-12-25T13:29:00Z' });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['en', '12/25/2025, 1:29 PM'],
    ['de', '25.12.2025, 13:29'],
    ['fr', '25/12/2025 13:29'],
])('date and time is formatted to the users browser language (%s)', async (lang, expected) => {
    setNavigatorLanguage(lang);

    const dateIndexField = makeDateIndexField({ date: '2025-12-25T13:29:00Z', time_enabled: true });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['en', '12/25/2025 – 12/28/2025'],
    ['de', '25.12.2025 – 28.12.2025'],
    ['fr', '25/12/2025 – 28/12/2025'],
])('date range is formatted to the users browser language (%s)', async (lang, expected) => {
    setNavigatorLanguage(lang);

    const dateIndexField = makeDateIndexField({
        start: '2025-12-25T02:13:00Z',
        end: '2025-12-28T03:59:00Z',
        mode: 'range',
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});
