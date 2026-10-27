import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import { CalendarDateTime, toZoned } from '@internationalized/date';
import DatePicker from '@/components/ui/DatePicker/DatePicker.vue';
import { config } from '@api';

window.__ = (key) => key;

config.initialize({ translationLocale: 'en' });

const makeDatePicker = (modelValue) => {
    return mount(DatePicker, {
        props: { modelValue, granularity: 'minute' },
        global: {
            mocks: {
                $date: { locale: 'en-US' },
            },
        },
    });
};

test('day period reflects the zoned date rather than the browser timezone', () => {
    process.env.TZ = 'America/New_York';

    const datePicker = makeDatePicker(toZoned(new CalendarDateTime(2024, 1, 20, 15, 30), 'Europe/London'));

    expect(datePicker.get('[data-reka-date-field-segment="hour"]').text()).toBe('3');
    expect(datePicker.get('[data-reka-date-field-segment="dayPeriod"]').text()).toBe('PM');
});

test('typing an hour keeps the day period of the zoned date', async () => {
    process.env.TZ = 'America/New_York';

    const modelValue = toZoned(new CalendarDateTime(2024, 1, 20, 15, 30), 'Europe/London');
    const datePicker = makeDatePicker(modelValue);

    await datePicker.get('[data-reka-date-field-segment="hour"]').trigger('keydown', { key: '5' });

    expect(datePicker.emitted('update:modelValue')[0][0].toString()).toBe(modelValue.set({ hour: 17 }).toString());
});
