import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { CalendarDateTime, toZoned } from '@internationalized/date';
import DateRangePicker from '@/components/ui/DateRangePicker/DateRangePicker.vue';
import { config } from '@api';

window.__ = (key) => key;

config.initialize({ translationLocale: 'en' });

const makeDateRangePicker = (modelValue) => {
    return mount(DateRangePicker, {
        props: { modelValue, granularity: 'minute' },
        global: {
            mocks: {
                $date: { locale: 'en-US' },
            },
        },
    });
};

const segment = (type, part) => `[data-reka-date-range-field-segment-type="${type}"][data-reka-date-field-segment="${part}"]`;

test('day periods reflect the zoned dates rather than the browser timezone', () => {
    process.env.TZ = 'America/New_York';

    const dateRangePicker = makeDateRangePicker({
        start: toZoned(new CalendarDateTime(2024, 1, 20, 15, 30), 'Europe/London'),
        end: toZoned(new CalendarDateTime(2024, 1, 21, 16, 30), 'Europe/London'),
    });

    expect(dateRangePicker.get(segment('start', 'hour')).text()).toBe('3');
    expect(dateRangePicker.get(segment('start', 'dayPeriod')).text()).toBe('PM');
    expect(dateRangePicker.get(segment('end', 'hour')).text()).toBe('4');
    expect(dateRangePicker.get(segment('end', 'dayPeriod')).text()).toBe('PM');
});

test('typing an hour keeps the day period of the zoned dates', async () => {
    process.env.TZ = 'America/New_York';

    const start = toZoned(new CalendarDateTime(2024, 1, 20, 15, 30), 'Europe/London');
    const end = toZoned(new CalendarDateTime(2024, 1, 21, 16, 30), 'Europe/London');
    const dateRangePicker = makeDateRangePicker({ start, end });

    await dateRangePicker.get(segment('end', 'hour')).trigger('keydown', { key: '5' });

    const emitted = dateRangePicker.emitted('update:modelValue')[0][0];
    expect(emitted.start.toString()).toBe(start.toString());
    expect(emitted.end.toString()).toBe(end.set({ hour: 17 }).toString());
});
