import { CalendarDate } from '@internationalized/date';

export function useCalendarDates() {
    const formatDateString = (date) => {
        return new Date(date.year, date.month - 1, date.day).toISOString().split('T')[0];
    };

    const createDateFromCalendarDate = (calendarDate, hours = 0, minutes = 0, seconds = 0) => {
        return new Date(calendarDate.year, calendarDate.month - 1, calendarDate.day, hours, minutes, seconds);
    };

    const getWeekDates = (currentDate) => {
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
    };

    const getVisibleHours = () => Array.from({length: 24}, (_, i) => i);

    const getHourLabel = (hour) => {
        if (hour === 0) return '12 AM';
        if (hour < 12) return `${hour} AM`;
        if (hour === 12) return '12 PM';
        return `${hour - 12} PM`;
    };

    const formatTime = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
    };

    const isToday = (date) => {
        const today = new Date();
        const compareDate = new Date(date.year, date.month - 1, date.day);
        return compareDate.toDateString() === today.toDateString();
    };

    const getCurrentDateRange = (currentDate, viewMode) => {
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
                endDate: new Date(weekDates[6].year, weekDates[6].month - 1, weekDates[6].day)
            };
        } else {
            return {
                startDate: new Date(currentDate.year, currentDate.month - 1, 1),
                endDate: new Date(currentDate.year, currentDate.month, 0)
            };
        }
    };

    return {
        formatDateString,
        createDateFromCalendarDate,
        getWeekDates,
        getVisibleHours,
        getHourLabel,
        formatTime,
        isToday,
        getCurrentDateRange
    };
}
