import { mount } from '@vue/test-utils';
import { test, expect } from 'vitest';
import DateFieldtype from '@/components/fieldtypes/DateFieldtype.vue';
import DateFormatter from '@statamic/components/DateFormatter.js';
import { containerContextKey } from '@statamic/components/ui/Publish/Container.vue';

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
                [containerContextKey]: {}
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
