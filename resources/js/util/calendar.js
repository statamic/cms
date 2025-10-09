import { CalendarDate } from '@internationalized/date';

export function formatDateString(date) {
    return new Date(date.year, date.month - 1, date.day).toISOString().split('T')[0];
}

export function getWeekDates(currentDate) {
    if (!currentDate) {
        console.warn('getWeekDates called with undefined currentDate');
        return [];
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

export function getHourLabel(hour) {
    if (hour === 0) return '12 AM';
    if (hour < 12) return `${hour} AM`;
    if (hour === 12) return '12 PM';
    return `${hour - 12} PM`;
}

export function formatTime(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
}

export function isToday(date) {
    const today = new Date();
    const compareDate = new Date(date.year, date.month - 1, date.day);
    return compareDate.toDateString() === today.toDateString();
}

export function getCurrentDateRange(currentDate, viewMode) {
    if (!currentDate || !viewMode) {
        console.warn('getCurrentDateRange called with undefined values:', { currentDate, viewMode });
        return { startDate: null, endDate: null };
    }

    if (viewMode === 'week') {
        const weekDates = getWeekDates(currentDate);
        if (!weekDates || weekDates.length < 7) {
            console.warn('getWeekDates returned invalid data:', weekDates);
            return { startDate: null, endDate: null };
        }
        return {
            startDate: new Date(weekDates[0].year, weekDates[0].month - 1, weekDates[0].day),
            endDate: new Date(weekDates[6].year, weekDates[6].month - 1, weekDates[6].day),
        };
    } else {
        // For month view, get the visible date range including days from adjacent months
        const firstDayOfMonth = new Date(currentDate.year, currentDate.month - 1, 1);
        const lastDayOfMonth = new Date(currentDate.year, currentDate.month, 0);

        // Calculate the first visible day (start of the week containing the 1st)
        const dayOfWeek = firstDayOfMonth.getDay();
        const startDate = new Date(firstDayOfMonth);
        startDate.setDate(firstDayOfMonth.getDate() - dayOfWeek);

        // Calculate the last visible day (end of the week containing the last day)
        const lastDayOfWeek = lastDayOfMonth.getDay();
        const endDate = new Date(lastDayOfMonth);
        endDate.setDate(lastDayOfMonth.getDate() + (6 - lastDayOfWeek));

        return {
            startDate,
            endDate,
        };
    }
}
