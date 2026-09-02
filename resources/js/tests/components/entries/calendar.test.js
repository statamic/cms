import { expect, test } from 'vitest';
import { getEntryDate } from '@/components/entries/calendar/calendar.js';

test('it parses datetime values', () => {
    const date = getEntryDate({ date: { date: '2026-08-24T12:30:00.000Z', format_has_time: true } });

    expect(date.toString().split('T')[0]).toBe('2026-08-24');
});

test('it parses date-only values', () => {
    const date = getEntryDate({ date: { date: '2026-08-24', format_has_time: false } });

    expect(date.toString()).toBe('2026-08-24');
});

test('it parses values that are plain strings', () => {
    expect(getEntryDate({ date: '2026-08-24T12:30:00.000Z' }).toString().split('T')[0]).toBe('2026-08-24');
    expect(getEntryDate({ date: '2026-08-24' }).toString()).toBe('2026-08-24');
});
