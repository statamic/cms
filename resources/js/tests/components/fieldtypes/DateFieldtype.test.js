import { mount } from '@vue/test-utils';
import { test, expect } from 'vitest';
import DateFieldtype from '@/components/fieldtypes/DateFieldtype.vue';
import TimeFieldtype from '@/components/fieldtypes/TimeFieldtype.vue';
import SvgIcon from '@/components/SvgIcon.vue';
import { createPinia } from 'pinia';

window.__ = (key) => key;

window.matchMedia = () => ({
    addEventListener: () => {},
});

let originalDate;
function setMockDate(dateString) {
    originalDate = Date; // Store the original Date object
    global.Date = class extends Date {
        constructor(...args) {
            if (args.length) return super(...args);
            return new originalDate(dateString);
        }
    };
}

const makeDateField = (props = {}) => {
    return mount(DateFieldtype, {
        props: {
            handle: 'date',
            config: {
                earliest_date: { date: null, time: null },
                latest_date: { date: null, time: null },
            },
            ...props,
        },
        components: {
            SvgIcon,
            TimeFieldtype,
        },
        plugins: [createPinia()],
        global: {
            provide: {
                store: '',
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
    ['UTC', '2025-12-25', '02:23'],
    ['America/New_York', '2025-12-24', '21:23'],
])('date and time is localized to the users timezone (%s)', async (tz, expectedDate, expectedTime) => {
    process.env.TZ = tz;

    const dateField = makeDateField({
        value: { date: '2025-12-25', time: '02:23' },
    });

    expect(dateField.vm.localValue).toMatchObject({
        date: expectedDate,
        time: expectedTime,
    });
});

test.each([
    {
        tz: 'UTC',
        expectedEmittedValue: { date: '2024-12-19', time: '00:00' },
        expectedLocalValue: { date: '2024-12-19', time: '00:00' },
    },
    {
        tz: 'America/New_York',
        expectedEmittedValue: { date: '2024-12-19', time: '05:00' },
        expectedLocalValue: { date: '2024-12-19', time: '00:00' },
    },
])('date can be updated ($tz)', async ({ tz, expectedEmittedValue, expectedLocalValue }) => {
    process.env.TZ = tz;

    const dateField = makeDateField({
        value: { date: '2025-12-25', time: '02:13' },
    });

    await dateField.vm.setLocalDate('2024-12-19');

    expect(dateField.emitted('update:value')[0][0]).toEqual(expectedEmittedValue);
    expect(dateField.vm.localValue).toMatchObject(expectedLocalValue);
});

test.each([
    {
        tz: 'UTC',
        expectedEmittedValue: { date: '2024-12-19', time: '02:13' },
        expectedLocalValue: { date: '2024-12-19', time: '02:13' },
    },
    {
        tz: 'America/New_York',
        expectedEmittedValue: { date: '2024-12-20', time: '02:13' },
        expectedLocalValue: { date: '2024-12-19', time: '21:13' },
    },
])(
    'date can be updated without resetting the time when time_enabled is true ($tz)',
    async ({ tz, expectedEmittedValue, expectedLocalValue }) => {
        process.env.TZ = tz;

        const dateField = makeDateField({
            config: {
                earliest_date: { date: null, time: null },
                latest_date: { date: null, time: null },
                time_enabled: true,
            },
            value: { date: '2025-12-25', time: '02:13' },
        });

        await dateField.vm.setLocalDate('2024-12-19');

        expect(dateField.emitted('update:value')[0][0]).toEqual(expectedEmittedValue);
        expect(dateField.vm.localValue).toMatchObject(expectedLocalValue);
    },
);

test.each([
    {
        tz: 'UTC',
        expectedEmittedValue: { date: '2025-12-25', time: '23:11' },
        expectedLocalValue: { date: '2025-12-25', time: '23:11' },
    },
    {
        tz: 'America/New_York',
        expectedEmittedValue: { date: '2025-12-25', time: '04:11' },
        expectedLocalValue: { date: '2025-12-24', time: '23:11' },
    },
])('time can be updated ($tz)', async ({ tz, expectedEmittedValue, expectedLocalValue }) => {
    process.env.TZ = tz;

    const dateField = makeDateField({
        config: {
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
            time_enabled: true,
        },
        value: { date: '2025-12-25', time: '02:13' },
    });

    await dateField.vm.setLocalTime('23:11');

    expect(dateField.emitted('update:value')[0][0]).toEqual(expectedEmittedValue);
    expect(dateField.vm.localValue).toMatchObject(expectedLocalValue);
});

test.each([
    {
        tz: 'UTC',
        expectedEmittedValue: { date: '2025-12-25', time: '23:11:29' },
        expectedLocalValue: { date: '2025-12-25', time: '23:11:29' },
    },
    {
        tz: 'America/New_York',
        expectedEmittedValue: { date: '2025-12-25', time: '04:11:29' },
        expectedLocalValue: { date: '2025-12-24', time: '23:11:29' },
    },
])('time with seconds can be updated ($tz)', async ({ tz, expectedEmittedValue, expectedLocalValue }) => {
    process.env.TZ = tz;

    const dateField = makeDateField({
        config: {
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
            time_seconds_enabled: true,
        },
        value: { date: '2025-12-25', time: '02:13:00' },
    });

    await dateField.vm.setLocalTime('23:11:29');

    expect(dateField.emitted('update:value')[0][0]).toEqual(expectedEmittedValue);
    expect(dateField.vm.localValue).toMatchObject(expectedLocalValue);
});

test.each([
    {
        tz: 'UTC',
        expectedEmittedValue: {
            start: { date: '2025-01-12', time: '00:00' },
            end: { date: '2025-01-14', time: '23:59' },
        },
        expectedLocalValue: {
            start: { date: '2025-01-12', time: '00:00' },
            end: { date: '2025-01-14', time: '23:59' },
        },
    },
    {
        tz: 'America/New_York',
        expectedEmittedValue: {
            start: { date: '2025-01-12', time: '05:00' },
            end: { date: '2025-01-15', time: '04:59' },
        },
        expectedLocalValue: {
            start: { date: '2025-01-12', time: '00:00' },
            end: { date: '2025-01-14', time: '23:59' },
        },
    },
])('date range can be updated ($tz)', async ({ tz, expectedEmittedValue, expectedLocalValue }) => {
    process.env.TZ = tz;

    const dateField = makeDateField({
        config: {
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
            mode: 'range',
        },
        value: {
            start: { date: '2025-12-25', time: '02:13' },
            end: { date: '2025-12-28', time: '09:04' },
        },
    });

    await dateField.vm.setLocalDate({
        start: '2025-01-12',
        end: '2025-01-14',
    });

    expect(dateField.emitted('update:value')[0][0]).toEqual(expectedEmittedValue);
    expect(dateField.vm.localValue).toMatchObject(expectedLocalValue);
});

test.each([
    [
        'UTC',
        { start: { date: '2021-12-25', time: '00:00' }, end: { date: '2021-12-25', time: '23:59' } },
        { start: { date: '2021-12-25', time: '00:00' }, end: { date: '2021-12-25', time: '23:59' } },
    ],
    [
        'America/New_York',
        { start: { date: '2021-12-25', time: '05:00' }, end: { date: '2021-12-26', time: '04:59' } },
        { start: { date: '2021-12-25', time: '00:00' }, end: { date: '2021-12-25', time: '23:59' } },
    ],
])(
    'required date range field with null value is automatically populated (%s)',
    async (tz, expectedEmittedValue, expectedLocalValue) => {
        process.env.TZ = tz;

        setMockDate('2021-12-25T12:13:14Z');

        const dateField = makeDateField({
            config: {
                earliest_date: { date: null, time: null },
                latest_date: { date: null, time: null },
                mode: 'range',
                required: true,
            },
            value: null,
        });

        await dateField.vm.$nextTick();
        expect(dateField.emitted('update:value')[0][0]).toEqual(expectedEmittedValue);
        expect(dateField.vm.localValue).toMatchObject(expectedLocalValue);
    },
);

test.each([
    ['UTC', '2025-12-25', '02:15'],
    ['America/New_York', '2025-12-24', '21:15'],
])('local time is updated when value prop is updated (%s)', async (tz, expectedDate, expectedTime) => {
    process.env.TZ = tz;

    const dateField = makeDateField({
        value: { date: '1984-01-01', time: '15:00' },
    });

    await dateField.setProps({ value: { date: '2025-12-25', time: '02:15' } });

    expect(dateField.vm.localValue).toMatchObject({
        date: expectedDate,
        time: expectedTime,
    });
});
