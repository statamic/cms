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

Object.defineProperty(Intl, 'DateTimeFormat', {
    value: () => ({
        resolvedOptions: () => ({
            timeZone: 'America/New_York', // UTC-5
        }),
    }),
});

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

test('date and time is localized to the users timezone', async () => {
    const dateField = makeDateField({
        value: { date: '2025-01-01', time: '15:00' },
    });

    expect(dateField.vm.localValue).toMatchObject({
        date: '2025-01-01',
        time: '10:00',
    });
});

test('date can be updated', async () => {
    const dateField = makeDateField({
        value: { date: '2025-01-01', time: '05:00' },
    });

    await dateField.vm.setLocalDate('2024-12-10');

    expect(dateField.emitted('update:value')[0]).toEqual([
        {
            date: '2024-12-10',
            time: '05:00',
        },
    ]);

    expect(dateField.vm.localValue).toMatchObject({
        date: '2024-12-10',
        time: '00:00',
    });
});

test('time can be updated', async () => {
    const dateField = makeDateField({
        value: { date: '2025-01-01', time: '15:00' },
    });

    await dateField.vm.setLocalTime('23:11');

    expect(dateField.emitted('update:value')[0]).toEqual([
        {
            date: '2025-01-02',
            time: '04:11',
        },
    ]);

    expect(dateField.vm.localValue).toMatchObject({
        date: '2025-01-01',
        time: '23:11',
    });
});

test('time with seconds can be updated', async () => {
    const dateField = makeDateField({
        config: {
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
            time_seconds_enabled: true,
        },
        value: { date: '2025-01-01', time: '15:00:00' },
    });

    await dateField.vm.setLocalTime('23:11:11');

    expect(dateField.emitted('update:value')[0]).toEqual([
        {
            date: '2025-01-02',
            time: '04:11:11',
        },
    ]);

    expect(dateField.vm.localValue).toMatchObject({
        date: '2025-01-01',
        time: '23:11:11',
    });
});

test('date range can be updated', async () => {
    const dateField = makeDateField({
        config: {
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
            mode: 'range',
        },
        value: {
            start: { date: '2025-01-01', time: '05:00' },
            end: { date: '2025-01-08', time: '04:59' },
        },
    });

    await dateField.vm.setLocalDate({
        start: '2025-01-01',
        end: '2025-01-30',
    });

    expect(dateField.emitted('update:value')[0]).toEqual([
        {
            start: { date: '2025-01-01', time: '05:00' },
            end: { date: '2025-01-31', time: '04:59' },
        },
    ]);

    expect(dateField.vm.localValue).toMatchObject({
        start: { date: '2025-01-01', time: '00:00' },
        end: { date: '2025-01-30', time: '23:59' },
    });
});

test('required date range field with null value is automatically populated', async () => {
    const dateField = makeDateField({
        config: {
            earliest_date: { date: null, time: null },
            latest_date: { date: null, time: null },
            mode: 'range',
            required: true,
        },
        value: null,
    });

    const today = new Date().toISOString().split('T')[0];

    expect(dateField.vm.localValue).toMatchObject({
        start: { date: today, time: '00:00' },
        end: { date: today, time: '23:59' },
    });
});

test('local time is updated when value prop is updated', async () => {
    const dateField = makeDateField({
        value: { date: '2025-01-01', time: '15:00' },
    });

    await dateField.setProps({ value: { date: '2025-01-01', time: '10:00' } });

    expect(dateField.vm.localValue).toMatchObject({
        date: '2025-01-01',
        time: '05:00',
    });
});
