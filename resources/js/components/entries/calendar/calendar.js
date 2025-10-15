import { CalendarDate, CalendarDateTime, fromDate, getLocalTimeZone } from '@internationalized/date';

export function formatDateString(date) {
    return new Date(date.year, date.month - 1, date.day).toISOString().split('T')[0];
}

export function getWeekDates(currentDate) {
    if (!currentDate) {
        throw new Error('getWeekDates called with undefined currentDate');
    }

    const currentWeekStart = new Date(currentDate.year, currentDate.month - 1, currentDate.day);
    const dayOfWeek = currentWeekStart.getDay();
    const startOfWeek = new Date(currentWeekStart);
    startOfWeek.setDate(currentWeekStart.getDate() - dayOfWeek);

    const weekDates = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(startOfWeek);
        date.setDate(startOfWeek.getDate() + i);
        weekDates.push(new CalendarDate(date.getFullYear(), date.getMonth() + 1, date.getDate()));
    }
    return weekDates;
}

export function getVisibleHours() {
    return Array.from({ length: 24 }, (_, i) => i);
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
    const firstDayOfMonth = new Date(date.year, date.month - 1, 1);
    const lastDayOfMonth = new Date(date.year, date.month, 0);

    // Calculate the first visible day (start of the week containing the 1st)
    const dayOfWeek = firstDayOfMonth.getDay();
    const startDate = new Date(firstDayOfMonth);
    startDate.setDate(firstDayOfMonth.getDate() - dayOfWeek);

    // Calculate the last visible day (end of the week containing the last day)
    const lastDayOfWeek = lastDayOfMonth.getDay();
    const endDate = new Date(lastDayOfMonth);
    endDate.setDate(lastDayOfMonth.getDate() + (6 - lastDayOfWeek));

    return { startDate, endDate };
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
