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
    });
};

test('date is localized to the users timezone', async () => {
    const dateIndexField = makeDateIndexField({
        date: '2025-01-01',
        time: '05:00',
        mode: 'single',
    });

    expect(dateIndexField.vm.formatted).toBe('January 1, 2025');
});

test('date and time is localized to the users timezone', async () => {
    const dateIndexField = makeDateIndexField({
        date: '2025-01-01',
        time: '15:00',
        mode: 'single',
        time_enabled: true,
    });

    expect(dateIndexField.vm.formatted).toBe('January 1, 2025 10:00 AM');
});

test('date range is localized to the users timezone', async () => {
    const dateIndexField = makeDateIndexField({
        start: { date: '2025-01-01', time: '05:00' },
        end: { date: '2025-01-11', time: '04:59' },
        mode: 'range',
    });

    expect(dateIndexField.vm.formatted).toBe('1/1/2025 – 1/10/2025');
});
