import { afterEach, expect, test } from 'vitest';
import { mount } from '@vue/test-utils';
import { h } from 'vue';
import { CalendarDateTime, toZoned } from '@internationalized/date';
import DatePicker from '@/components/ui/DatePicker/DatePicker.vue';
import { config } from '@api';
import '../../../css/app.css';

window.__ = (key) => key;
config.initialize({ translationLocale: 'en' });

let wrapper;
let host;
afterEach(() => {
    wrapper?.unmount();
    host?.remove();
});

const mountDatePicker = (width, props = {}) => {
    host = document.createElement('div');
    host.style.width = width;
    document.body.appendChild(host);

    wrapper = mount(
        {
            render: () =>
                h(DatePicker, {
                    modelValue: toZoned(new CalendarDateTime(2026, 11, 25, 6, 38, 45), 'Europe/London'),
                    granularity: 'minute',
                    ...props,
                }),
        },
        { attachTo: host, global: { mocks: { __: (key) => key, $date: { locale: 'en-GB' } } } },
    );

    return wrapper.find('[data-ui-input]').element;
};

const fit = (root) => root.getAttribute('data-fit');
const overflows = (root) => {
    const row = root.querySelector('[data-date-picker-box]');
    return row.scrollWidth > row.clientWidth;
};

const waitFor = async (condition) => {
    for (let i = 0; i < 50 && !condition(); i++) {
        await new Promise((resolve) => setTimeout(resolve, 20));
    }
};

test('a wide date and time stays at full size', async () => {
    const root = mountDatePicker('480px');

    await new Promise((resolve) => setTimeout(resolve, 200));

    expect(fit(root)).toBe('');
    expect(overflows(root)).toBe(false);
});

test('a narrow date and time steps down until it fits', async () => {
    const root = mountDatePicker('220px');

    await waitFor(() => fit(root) !== '');

    expect(fit(root)).toContain('compact');
    expect(overflows(root)).toBe(false);
});

test('it goes back to compact and scrolls when nothing fits', async () => {
    const root = mountDatePicker('150px', { granularity: 'second' });

    await waitFor(() => fit(root) === 'compact');

    expect(fit(root)).toBe('compact');
    expect(overflows(root)).toBe(true);
});

test('it refits as it is resized', async () => {
    const root = mountDatePicker('480px');

    host.style.width = '220px';
    await waitFor(() => fit(root) !== '');
    expect(fit(root)).toContain('compact');

    host.style.width = '480px';
    await waitFor(() => fit(root) === '');
    expect(fit(root)).toBe('');
});

test('a date without a time is never fitted', async () => {
    const root = mountDatePicker('150px', { granularity: 'day' });

    await new Promise((resolve) => setTimeout(resolve, 200));

    expect(root.hasAttribute('data-fit')).toBe(false);
});
