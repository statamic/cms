import { mount } from '@vue/test-utils';
import { expect, test } from 'vitest';
import Timezones from '@/components/ui/Timezones.vue';
import DateFormatter from '@/components/DateFormatter.js';
import { config } from '@api';

config.initialize({ translationLocale: 'en', displayTimezone: 'UTC' });
DateFormatter.defaultLocale = 'en-US';

const makeTimezones = (props) => mount(Timezones, { props });

const date = '2026-05-05T12:34:27.000Z';
const range = { start: '2026-05-05T12:34:27.000Z', end: '2026-05-08T17:30:42.000Z' };

test('seconds are shown by default', () => {
    expect(makeTimezones({ date }).text()).toContain('12:34:27');
});

test('seconds can be hidden', () => {
    const text = makeTimezones({ date, showSeconds: false }).text();

    expect(text).toContain('12:34');
    expect(text).not.toContain(':27');
});

test('seconds are shown for ranges by default', () => {
    const text = makeTimezones({ date: range }).text();

    expect(text).toContain('12:34:27');
    expect(text).toContain('5:30:42');
});

test('seconds can be hidden for ranges', () => {
    const text = makeTimezones({ date: range, showSeconds: false }).text();

    expect(text).toContain('12:34');
    expect(text).toContain('5:30');
    expect(text).not.toContain(':27');
    expect(text).not.toContain(':42');
});
