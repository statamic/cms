import { CalendarDate, CalendarDateTime, fromDate, getLocalTimeZone, startOfWeek, endOfWeek } from '@internationalized/date';
import DateFormatter from '@/components/DateFormatter.js';

export function formatDateString(date) {
    return new Date(date.year, date.month - 1, date.day).toISOString().split('T')[0];
}

export function getWeekDates(currentDate) {
    if (!currentDate) {
        throw new Error('getWeekDates called with undefined currentDate');
    }

    const weekStart = startOfWeek(currentDate, new DateFormatter().locale);

    const weekDates = [];
    for (let i = 0; i < 7; i++) {
        weekDates.push(weekStart.add({ days: i }));
    }
    return weekDates;
}

export function isToday(date) {
    const today = new Date();
    const compareDate = new Date(date.year, date.month - 1, date.day);
    return compareDate.toDateString() === today.toDateString();
}

export function getCurrentDateRange(currentDate, viewMode) {
    if (!currentDate || !viewMode) {
        throw new Error('getCurrentDateRange called with undefined values');
    }

    return viewMode === 'week' ? getWeekDateRange(currentDate) : getMonthDateRange(currentDate);
}

function getWeekDateRange(date) {
    const weekDates = getWeekDates(date);

    return {
        startDate: new Date(weekDates[0].year, weekDates[0].month - 1, weekDates[0].day),
        endDate: new Date(weekDates[6].year, weekDates[6].month - 1, weekDates[6].day),
    };
}

function getMonthDateRange(date) {
    // Get the visible date range including days from adjacent months
    const locale = new DateFormatter().locale;

    // Get the first and last day of the month
    const firstDayOfMonth = new CalendarDate(date.year, date.month, 1);
    const lastDayOfMonth = new CalendarDate(date.year, date.month, date.calendar.getDaysInMonth(date));

    // Calculate the first visible day (start of the week containing the 1st)
    const weekStartOfFirst = startOfWeek(firstDayOfMonth, locale);

    // Calculate the last visible day (end of the week containing the last day)
    const weekEndOfLast = endOfWeek(lastDayOfMonth, locale);

    return {
        startDate: new Date(weekStartOfFirst.year, weekStartOfFirst.month - 1, weekStartOfFirst.day),
        endDate: new Date(weekEndOfLast.year, weekEndOfLast.month - 1, weekEndOfLast.day)
    };
}

export function getCreateUrlDateParam(date, hour) {
    // The date argument is a CalendarDate object, which has no timezone so we assume it's the local timezone.
    // The server expects the date to be in UTC timezone, so we convert it.
    if (hour) date = new CalendarDateTime(date.year, date.month, date.day, hour, 0);
    const localDate = date.toDate(getLocalTimeZone());
    const d = fromDate(localDate, 'UTC');
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.year}-${pad(d.month)}-${pad(d.day)}-${pad(d.hour)}${pad(d.minute)}`;
}
