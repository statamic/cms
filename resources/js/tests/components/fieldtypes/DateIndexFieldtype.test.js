import { mount } from '@vue/test-utils';
import { test, expect } from 'vitest';
import DateIndexFieldtype from '@/components/fieldtypes/DateIndexFieldtype.vue';
import Moment from 'moment';

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
        global: {
            mocks: {
                $moment: (date) => {
                    return Moment(date);
                },
            },
        },
    });
};

test.each([
    ['UTC', '2025-12-25'],
    ['America/New_York', '2025-12-24'],
])('date is localized to the users timezone (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        date: '2025-12-25',
        time: '02:13',
        mode: 'single',
        display_format: 'YYYY-MM-DD',
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['UTC', '2025-12-25 02:13'],
    ['America/New_York', '2025-12-24 21:13'],
])('date and time is localized to the users timezone (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        date: '2025-12-25',
        time: '02:13',
        mode: 'single',
        display_format: 'YYYY-MM-DD HH:mm',
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['UTC', '2025-12-25 – 2025-12-28'],
    ['America/New_York', '2025-12-24 – 2025-12-27'],
])('date range is localized to the users timezone (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        start: { date: '2025-12-25', time: '02:13' },
        end: { date: '2025-12-28', time: '03:59' },
        mode: 'range',
        display_format: 'YYYY-MM-DD',
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});

test.each([
    ['UTC', '25/12/2025 02:13:15'],
    ['America/New_York', '24/12/2025 21:13:15'],
])('configured display format is respected (%s)', async (tz, expected) => {
    process.env.TZ = tz;

    const dateIndexField = makeDateIndexField({
        date: '2025-12-25',
        time: '02:13:15',
        mode: 'single',
        display_format: 'DD/MM/YYYY HH:mm:ss',
    });

    expect(dateIndexField.vm.formatted).toBe(expected);
});
