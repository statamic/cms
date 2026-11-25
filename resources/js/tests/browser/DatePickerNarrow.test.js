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

const mountDatePicker = async (width, props = {}) => {
    host = document.createElement('div');
    host.style.width = width;
    document.body.appendChild(host);

    wrapper = mount(
        {
            render: () =>
                h(DatePicker, {
                    modelValue: toZoned(new CalendarDateTime(2026, 11, 25, 6, 38), 'Europe/London'),
                    granularity: 'minute',
                    ...props,
                }),
        },
        { attachTo: host, global: { mocks: { __: (key) => key, $date: { locale: 'en-GB' } }, directives: { tooltip: {} } } },
    );

    await new Promise((resolve) => requestAnimationFrame(resolve));

    return wrapper.find('[data-ui-input]').element;
};

const dateSize = (root) => getComputedStyle(root.querySelector('[data-date-picker-date]')).fontSize;
const triggerWidth = (root) => root.querySelector('[data-date-picker-trigger]').getBoundingClientRect().width;

test('a wide date and time is full size', async () => {
    const root = await mountDatePicker('480px');

    expect(dateSize(root)).toBe('16px');
    expect(triggerWidth(root)).toBe(32);
});

test('a 33% width date and time has a 14px date', async () => {
    const root = await mountDatePicker('300px');

    expect(dateSize(root)).toBe('14px');
    expect(triggerWidth(root)).toBe(32);
});

test('a 25% width read-only date and time is compact', async () => {
    const root = await mountDatePicker('220px', { readOnly: true });

    expect(dateSize(root)).toBe('13.2px');
    expect(triggerWidth(root)).toBe(20);
});

test('a 25% width date and time with the clear button is tight', async () => {
    const root = await mountDatePicker('220px');

    expect(triggerWidth(root)).toBe(12);
});

test('it changes size as it is resized', async () => {
    const root = await mountDatePicker('480px');

    host.style.width = '220px';
    await new Promise((resolve) => requestAnimationFrame(resolve));
    expect(triggerWidth(root)).toBe(12);

    host.style.width = '480px';
    await new Promise((resolve) => requestAnimationFrame(resolve));
    expect(triggerWidth(root)).toBe(32);
});

test('a date without a time keeps its size', async () => {
    const root = await mountDatePicker('150px', { granularity: 'day' });

    expect(root.hasAttribute('data-date-picker-fits-width')).toBe(false);
    expect(dateSize(root)).toBe('16px');
});
