import { mount } from '@vue/test-utils';
import { test, expect } from 'vitest';
import DateIndexFieldtype from '@/components/fieldtypes/DateIndexFieldtype.vue';
import Moment from 'moment';

window.__ = (key) => key;

window.matchMedia = () => ({
    addEventListener: () => {},
});

process.env.TZ = 'America/New_York';

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

test('date is localized to the users timezone', async () => {
    const dateIndexField = makeDateIndexField({
        date: '2025-01-01',
        time: '05:00',
        mode: 'single',
        display_format: 'YYYY-MM-DD',
    });

    expect(dateIndexField.vm.formatted).toBe('2025-01-01');
});

test('date and time is localized to the users timezone', async () => {
    const dateIndexField = makeDateIndexField({
        date: '2025-01-01',
        time: '15:00',
        mode: 'single',
        display_format: 'YYYY-MM-DD HH:mm',
    });

    expect(dateIndexField.vm.formatted).toBe('2025-01-01 10:00');
});

test('date range is localized to the users timezone', async () => {
    const dateIndexField = makeDateIndexField({
        start: { date: '2025-01-01', time: '05:00' },
        end: { date: '2025-01-11', time: '04:59' },
        mode: 'range',
        display_format: 'YYYY-MM-DD',
    });

    expect(dateIndexField.vm.formatted).toBe('2025-01-01 – 2025-01-10');
});
