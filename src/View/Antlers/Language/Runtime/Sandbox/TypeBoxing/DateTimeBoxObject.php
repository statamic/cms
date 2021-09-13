<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\TypeBoxing;

use BadMethodCallException;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Traits\Macroable;

class DateTimeBoxObject
{
    use Macroable, AntlersBoxedStandardMethods;

    /**
     * @var Carbon|null
     */
    protected $value = null;

    public function __construct($value)
    {
        $this->value = $value;
    }

    protected $forwardDynamicCalls = [
        'isUtc' => 1,
        'isLocal' => 1,
        'isValid' => 1,
        'isDST' => 1,
        'isSunday' => 1,
        'isMonday' => 1,
        'isTuesday' => 1,
        'isWednesday' => 1,
        'isThursday' => 1,
        'isFriday' => 1,
        'isSaturday' => 1,
        'isSameYear' => 1,
        'isCurrentYear' => 1,
        'isNextYear' => 1,
        'isLastYear' => 1,
        'isSameWeek' => 1,
        'isCurrentWeek' => 1,
        'isNextWeek' => 1,
        'isLastWeek' => 1,
        'isSameDay' => 1,
        'isCurrentDay' => 1,
        'isNextDay' => 1,
        'isLastDay' => 1,
        'isSameHour' => 1,
        'isCurrentHour' => 1,
        'isNextHour' => 1,
        'isLastHour' => 1,
        'isSameMinute' => 1,
        'isCurrentMinute' => 1,
        'isNextMinute' => 1,
        'isLastMinute' => 1,
        'isSameSecond' => 1,
        'isCurrentSecond' => 1,
        'isNextSecond' => 1,
        'isLastSecond' => 1,
        'isSameMicro' => 1,
        'isCurrentMicro' => 1,
        'isNextMicro' => 1,
        'isLastMicro' => 1,
        'isSameMicrosecond' => 1,
        'isCurrentMicrosecond' => 1,
        'isNextMicrosecond' => 1,
        'isLastMicrosecond' => 1,
        'isCurrentMonth' => 1,
        'isNextMonth' => 1,
        'isLastMonth' => 1,
        'isCurrentQuarter' => 1,
        'isNextQuarter' => 1,
        'isLastQuarter' => 1,
        'isSameDecade' => 1,
        'isCurrentDecade' => 1,
        'isNextDecade' => 1,
        'isLastDecade' => 1,
        'isSameCentury' => 1,
        'isCurrentCentury' => 1,
        'isNextCentury' => 1,
        'isLastCentury' => 1,
        'isSameMillennium' => 1,
        'isCurrentMillennium' => 1,
        'isNextMillennium' => 1,
        'isLastMillennium' => 1,
        'years' => 1,
        'year' => 1,
        'setYears' => 1,
        'setYear' => 1,
        'months' => 1,
        'month' => 1,
        'setMonths' => 1,
        'setMonth' => 1,
        'days' => 1,
        'day' => 1,
        'setDays' => 1,
        'setDay' => 1,
        'hours' => 1,
        'hour' => 1,
        'setHours' => 1,
        'setHour' => 1,
        'minutes' => 1,
        'minute' => 1,
        'setMinutes' => 1,
        'setMinute' => 1,
        'seconds' => 1,
        'second' => 1,
        'setSeconds' => 1,
        'setSecond' => 1,
        'millis' => 1,
        'milli' => 1,
        'setMillis' => 1,
        'setMilli' => 1,
        'milliseconds' => 1,
        'millisecond' => 1,
        'setMilliseconds' => 1,
        'setMillisecond' => 1,
        'micros' => 1,
        'micro' => 1,
        'setMicros' => 1,
        'setMicro' => 1,
        'microseconds' => 1,
        'microsecond' => 1,
        'setMicroseconds' => 1,
        'setMicrosecond' => 1,
        'addYears' => 1,
        'addYear' => 1,
        'subYears' => 1,
        'subYear' => 1,
        'addYearsWithOverflow' => 1,
        'addYearWithOverflow' => 1,
        'subYearsWithOverflow' => 1,
        'subYearWithOverflow' => 1,
        'addYearsWithoutOverflow' => 1,
        'addYearWithoutOverflow' => 1,
        'subYearsWithoutOverflow' => 1,
        'subYearWithoutOverflow' => 1,
        'addYearsWithNoOverflow' => 1,
        'addYearWithNoOverflow' => 1,
        'subYearsWithNoOverflow' => 1,
        'subYearWithNoOverflow' => 1,
        'addYearsNoOverflow' => 1,
        'addYearNoOverflow' => 1,
        'subYearsNoOverflow' => 1,
        'subYearNoOverflow' => 1,
        'addMonths' => 1,
        'addMonth' => 1,
        'subMonths' => 1,
        'subMonth' => 1,
        'addMonthsWithOverflow' => 1,
        'addMonthWithOverflow' => 1,
        'subMonthsWithOverflow' => 1,
        'subMonthWithOverflow' => 1,
        'addMonthsWithoutOverflow' => 1,
        'addMonthWithoutOverflow' => 1,
        'subMonthsWithoutOverflow' => 1,
        'subMonthWithoutOverflow' => 1,
        'addMonthsWithNoOverflow' => 1,
        'addMonthWithNoOverflow' => 1,
        'subMonthsWithNoOverflow' => 1,
        'subMonthWithNoOverflow' => 1,
        'addMonthsNoOverflow' => 1,
        'addMonthNoOverflow' => 1,
        'subMonthsNoOverflow' => 1,
        'subMonthNoOverflow' => 1,
        'addDays' => 1,
        'addDay' => 1,
        'subDays' => 1,
        'subDay' => 1,
        'addHours' => 1,
        'addHour' => 1,
        'subHours' => 1,
        'subHour' => 1,
        'addMinutes' => 1,
        'addMinute' => 1,
        'subMinutes' => 1,
        'subMinute' => 1,
        'addSeconds' => 1,
        'addSecond' => 1,
        'subSeconds' => 1,
        'subSecond' => 1,
        'addMillis' => 1,
        'addMilli' => 1,
        'subMillis' => 1,
        'subMilli' => 1,
        'addMilliseconds' => 1,
        'addMillisecond' => 1,
        'subMilliseconds' => 1,
        'subMillisecond' => 1,
        'addMicros' => 1,
        'addMicro' => 1,
        'subMicros' => 1,
        'subMicro' => 1,
        'addMicroseconds' => 1,
        'addMicrosecond' => 1,
        'subMicroseconds' => 1,
        'subMicrosecond' => 1,
        'addMillennia' => 1,
        'addMillennium' => 1,
        'subMillennia' => 1,
        'subMillennium' => 1,
        'addMillenniaWithOverflow' => 1,
        'addMillenniumWithOverflow' => 1,
        'subMillenniaWithOverflow' => 1,
        'subMillenniumWithOverflow' => 1,
        'addMillenniaWithoutOverflow' => 1,
        'addMillenniumWithoutOverflow' => 1,
        'subMillenniaWithoutOverflow' => 1,
        'subMillenniumWithoutOverflow' => 1,
        'addMillenniaWithNoOverflow' => 1,
        'addMillenniumWithNoOverflow' => 1,
        'subMillenniaWithNoOverflow' => 1,
        'subMillenniumWithNoOverflow' => 1,
        'addMillenniaNoOverflow' => 1,
        'addMillenniumNoOverflow' => 1,
        'subMillenniaNoOverflow' => 1,
        'subMillenniumNoOverflow' => 1,
        'addCenturies' => 1,
        'addCentury' => 1,
        'subCenturies' => 1,
        'subCentury' => 1,
        'addCenturiesWithOverflow' => 1,
        'addCenturyWithOverflow' => 1,
        'subCenturiesWithOverflow' => 1,
        'subCenturyWithOverflow' => 1,
        'addCenturiesWithoutOverflow' => 1,
        'addCenturyWithoutOverflow' => 1,
        'subCenturiesWithoutOverflow' => 1,
        'subCenturyWithoutOverflow' => 1,
        'addCenturiesWithNoOverflow' => 1,
        'addCenturyWithNoOverflow' => 1,
        'subCenturiesWithNoOverflow' => 1,
        'subCenturyWithNoOverflow' => 1,
        'addCenturiesNoOverflow' => 1,
        'addCenturyNoOverflow' => 1,
        'subCenturiesNoOverflow' => 1,
        'subCenturyNoOverflow' => 1,
        'addDecades' => 1,
        'addDecade' => 1,
        'subDecades' => 1,
        'subDecade' => 1,
        'addDecadesWithOverflow' => 1,
        'addDecadeWithOverflow' => 1,
        'subDecadesWithOverflow' => 1,
        'subDecadeWithOverflow' => 1,
        'addDecadesWithoutOverflow' => 1,
        'addDecadeWithoutOverflow' => 1,
        'subDecadesWithoutOverflow' => 1,
        'subDecadeWithoutOverflow' => 1,
        'addDecadesWithNoOverflow' => 1,
        'addDecadeWithNoOverflow' => 1,
        'subDecadesWithNoOverflow' => 1,
        'subDecadeWithNoOverflow' => 1,
        'addDecadesNoOverflow' => 1,
        'addDecadeNoOverflow' => 1,
        'subDecadesNoOverflow' => 1,
        'subDecadeNoOverflow' => 1,
        'addQuarters' => 1,
        'addQuarter' => 1,
        'subQuarters' => 1,
        'subQuarter' => 1,
        'addQuartersWithOverflow' => 1,
        'addQuarterWithOverflow' => 1,
        'subQuartersWithOverflow' => 1,
        'subQuarterWithOverflow' => 1,
        'addQuartersWithoutOverflow' => 1,
        'addQuarterWithoutOverflow' => 1,
        'subQuartersWithoutOverflow' => 1,
        'subQuarterWithoutOverflow' => 1,
        'addQuartersWithNoOverflow' => 1,
        'addQuarterWithNoOverflow' => 1,
        'subQuartersWithNoOverflow' => 1,
        'subQuarterWithNoOverflow' => 1,
        'addQuartersNoOverflow' => 1,
        'addQuarterNoOverflow' => 1,
        'subQuartersNoOverflow' => 1,
        'subQuarterNoOverflow' => 1,
        'addWeeks' => 1,
        'addWeek' => 1,
        'subWeeks' => 1,
        'subWeek' => 1,
        'addWeekdays' => 1,
        'addWeekday' => 1,
        'subWeekdays' => 1,
        'subWeekday' => 1,
        'addRealMicros' => 1,
        'addRealMicro' => 1,
        'subRealMicros' => 1,
        'subRealMicro' => 1,
        'microsUntil' => 1,
        'addRealMicroseconds' => 1,
        'addRealMicrosecond' => 1,
        'subRealMicroseconds' => 1,
        'subRealMicrosecond' => 1,
        'microsecondsUntil' => 1,
        'addRealMillis' => 1,
        'addRealMilli' => 1,
        'subRealMillis' => 1,
        'subRealMilli' => 1,
        'millisUntil' => 1,
        'addRealMilliseconds' => 1,
        'addRealMillisecond' => 1,
        'subRealMilliseconds' => 1,
        'subRealMillisecond' => 1,
        'millisecondsUntil' => 1,
        'addRealSeconds' => 1,
        'addRealSecond' => 1,
        'subRealSeconds' => 1,
        'subRealSecond' => 1,
        'secondsUntil' => 1,
        'addRealMinutes' => 1,
        'addRealMinute' => 1,
        'subRealMinutes' => 1,
        'subRealMinute' => 1,
        'minutesUntil' => 1,
        'addRealHours' => 1,
        'addRealHour' => 1,
        'subRealHours' => 1,
        'subRealHour' => 1,
        'hoursUntil' => 1,
        'addRealDays' => 1,
        'addRealDay' => 1,
        'subRealDays' => 1,
        'subRealDay' => 1,
        'daysUntil' => 1,
        'addRealWeeks' => 1,
        'addRealWeek' => 1,
        'subRealWeeks' => 1,
        'subRealWeek' => 1,
        'weeksUntil' => 1,
        'addRealMonths' => 1,
        'addRealMonth' => 1,
        'subRealMonths' => 1,
        'subRealMonth' => 1,
        'monthsUntil' => 1,
        'addRealQuarters' => 1,
        'addRealQuarter' => 1,
        'subRealQuarters' => 1,
        'subRealQuarter' => 1,
        'quartersUntil' => 1,
        'addRealYears' => 1,
        'addRealYear' => 1,
        'subRealYears' => 1,
        'subRealYear' => 1,
        'yearsUntil' => 1,
        'addRealDecades' => 1,
        'addRealDecade' => 1,
        'subRealDecades' => 1,
        'subRealDecade' => 1,
        'decadesUntil' => 1,
        'addRealCenturies' => 1,
        'addRealCentury' => 1,
        'subRealCenturies' => 1,
        'subRealCentury' => 1,
        'centuriesUntil' => 1,
        'addRealMillennia' => 1,
        'addRealMillennium' => 1,
        'subRealMillennia' => 1,
        'subRealMillennium' => 1,
        'millenniaUntil' => 1,
        'roundYear' => 1,
        'roundYears' => 1,
        'floorYear' => 1,
        'floorYears' => 1,
        'ceilYear' => 1,
        'ceilYears' => 1,
        'roundMonth' => 1,
        'roundMonths' => 1,
        'floorMonth' => 1,
        'floorMonths' => 1,
        'ceilMonth' => 1,
        'ceilMonths' => 1,
        'roundDay' => 1,
        'roundDays' => 1,
        'floorDay' => 1,
        'floorDays' => 1,
        'ceilDay' => 1,
        'ceilDays' => 1,
        'roundHour' => 1,
        'roundHours' => 1,
        'floorHour' => 1,
        'floorHours' => 1,
        'ceilHour' => 1,
        'ceilHours' => 1,
        'roundMinute' => 1,
        'roundMinutes' => 1,
        'floorMinute' => 1,
        'floorMinutes' => 1,
        'ceilMinute' => 1,
        'ceilMinutes' => 1,
        'roundSecond' => 1,
        'roundSeconds' => 1,
        'floorSecond' => 1,
        'floorSeconds' => 1,
        'ceilSecond' => 1,
        'ceilSeconds' => 1,
        'roundMillennium' => 1,
        'roundMillennia' => 1,
        'floorMillennium' => 1,
        'floorMillennia' => 1,
        'ceilMillennium' => 1,
        'ceilMillennia' => 1,
        'roundCentury' => 1,
        'roundCenturies' => 1,
        'floorCentury' => 1,
        'floorCenturies' => 1,
        'ceilCentury' => 1,
        'ceilCenturies' => 1,
        'roundDecade' => 1,
        'roundDecades' => 1,
        'floorDecade' => 1,
        'floorDecades' => 1,
        'ceilDecade' => 1,
        'ceilDecades' => 1,
        'roundQuarter' => 1,
        'roundQuarters' => 1,
        'floorQuarter' => 1,
        'floorQuarters' => 1,
        'ceilQuarter' => 1,
        'ceilQuarters' => 1,
        'roundMillisecond' => 1,
        'roundMilliseconds' => 1,
        'floorMillisecond' => 1,
        'floorMilliseconds' => 1,
        'ceilMillisecond' => 1,
        'ceilMilliseconds' => 1,
        'roundMicrosecond' => 1,
        'roundMicroseconds' => 1,
        'floorMicrosecond' => 1,
        'floorMicroseconds' => 1,
        'ceilMicrosecond' => 1,
        'ceilMicroseconds' => 1,
    ];

    public function format($format)
    {
        return $this->value->format($format);
    }

    public function modify($modify)
    {
        return $this->value->modify($modify);
    }

    public function getTimezone()
    {
        return $this->value->getTimezone();
    }

    public function setTimezone($value)
    {
        return $this->value->setTimezone($value);
    }

    public function getOffset()
    {
        return $this->value->getOffset();
    }

    public function setTime($hour, $minute, $second = null, $microseconds = null)
    {
        return $this->value->setTime($hour, $minute, $second, $microseconds);
    }

    public function setDate($year, $month, $day)
    {
        return $this->value->setDate($year, $month, $day);
    }

    public function setISODate($year, $week, $day = 1)
    {
        return $this->value->setISODate($year, $week, $day);
    }

    public function setTimestamp($unixTimestamp)
    {
        return $this->value->setTimestamp($unixTimestamp);
    }

    public function getTimestamp()
    {
        return $this->value->getTimestamp();
    }

    public function diff($date = null, $absolute = null)
    {
        return $this->value->diff($date, $absolute);
    }

    public function copy()
    {
        return $this->value->copy();
    }

    public function clone()
    {
        return $this->value->clone();
    }

    public function nowWithSameTz()
    {
        return $this->value->nowWithSameTz();
    }

    public function carbonize($date = null)
    {
        return $this->value->carbonize($date);
    }

    public function get($name)
    {
        return $this->value->get($name);
    }

    public function set($name, $value = null)
    {
        return $this->value->set($name, $value);
    }

    public function getTranslatedDayName($context = null, $keySuffix = null, $defaultValue = null)
    {
        return $this->value->getTranslatedDayName($context, $keySuffix, $defaultValue);
    }

    public function getTranslatedShortDayName($context = null)
    {
        return $this->value->getTranslatedShortDayName($context);
    }

    public function getTranslatedMinDayName($context = null)
    {
        return $this->value->getTranslatedMinDayName($context);
    }

    public function getTranslatedMonthName($context = null, $keySuffix = null, $defaultValue = null)
    {
        return $this->value->getTranslatedMonthName($context, $keySuffix, $defaultValue);
    }

    public function getTranslatedShortMonthName($context = null)
    {
        return $this->value->getTranslatedShortMonthName($context);
    }

    public function dayOfYear($value = null)
    {
        return $this->value->dayOfYear($value);
    }

    public function weekday($value = null)
    {
        return $this->value->weekday($value);
    }

    public function isoWeekday($value = null)
    {
        return $this->value->isoWeekday($value);
    }

    public function setUnitNoOverflow($valueUnit, $value, $overflowUnit)
    {
        return $this->value->setUnitNoOverflow($valueUnit, $value, $overflowUnit);
    }

    public function addUnitNoOverflow($valueUnit, $value, $overflowUnit)
    {
        return $this->value->addUnitNoOverflow($valueUnit, $value, $overflowUnit);
    }

    public function subUnitNoOverflow($valueUnit, $value, $overflowUnit)
    {
        return $this->value->subUnitNoOverflow($valueUnit, $value, $overflowUnit);
    }

    public function utcOffset($minuteOffset = null)
    {
        return $this->value->utcOffset($minuteOffset);
    }

    public function setDateTime($year, $month, $day, $hour, $minute, $second = null, $microseconds = null)
    {
        return $this->value->setDateTime($year, $month, $day, $hour, $minute, $second, $microseconds);
    }

    public function setTimeFromTimeString($time)
    {
        return $this->value->setTimeFromTimeString($time);
    }

    public function timezone($value)
    {
        return $this->value->timezone($value);
    }

    public function tz($value = null)
    {
        return $this->value->tz($value);
    }

    public function shiftTimezone($value)
    {
        return $this->value->shiftTimezone($value);
    }

    public function utc()
    {
        return $this->value->utc();
    }

    public function setDateFrom($date = null)
    {
        return $this->value->setDateFrom($date);
    }

    public function setTimeFrom($date = null)
    {
        return $this->value->setTimeFrom($date);
    }

    public function setDateTimeFrom($date = null)
    {
        return $this->value->setDateTimeFrom($date);
    }

    public function formatLocalized($format)
    {
        return $this->value->formatLocalized($format);
    }

    public function getIsoFormats($locale = null)
    {
        return $this->value->getIsoFormats($locale);
    }

    public function getCalendarFormats($locale = null)
    {
        return $this->value->getCalendarFormats($locale);
    }

    public function getPaddedUnit($unit, $length = 2, $padString = '0', $padType = null)
    {
        return $this->value->getPaddedUnit($unit, $length, $padString, $padType);
    }

    public function ordinal($key, $period = null)
    {
        return $this->value->ordinal($key, $period);
    }

    public function meridiem($isLower = null)
    {
        return $this->value->meridiem($isLower);
    }

    public function getAltNumber($key)
    {
        return $this->value->getAltNumber($key);
    }

    public function isoFormat($format, $originalFormat = null)
    {
        return $this->value->isoFormat($format, $originalFormat);
    }

    public function translatedFormat($format)
    {
        return $this->value->translatedFormat($format);
    }

    public function getOffsetString($separator = ':')
    {
        return $this->value->getOffsetString($separator);
    }

    public function setUnit($unit, $value = null)
    {
        return $this->value->setUnit($unit, $value);
    }

    public function startOfDay()
    {
        return $this->value->startOfDay();
    }

    public function endOfDay()
    {
        return $this->value->endOfDay();
    }

    public function startOfMonth()
    {
        return $this->value->startOfMonth();
    }

    public function endOfMonth()
    {
        return $this->value->endOfMonth();
    }

    public function startOfQuarter()
    {
        return $this->value->startOfQuarter();
    }

    public function endOfQuarter()
    {
        return $this->value->endOfQuarter();
    }

    public function startOfYear()
    {
        return $this->value->startOfYear();
    }

    public function endOfYear()
    {
        return $this->value->endOfYear();
    }

    public function startOfDecade()
    {
        return $this->value->startOfDecade();
    }

    public function endOfDecade()
    {
        return $this->value->endOfDecade();
    }

    public function startOfCentury()
    {
        return $this->value->startOfCentury();
    }

    public function endOfCentury()
    {
        return $this->value->endOfCentury();
    }

    public function startOfMillennium()
    {
        return $this->value->startOfMillennium();
    }

    public function endOfMillennium()
    {
        return $this->value->endOfMillennium();
    }

    public function startOfWeek($weekStartsAt = null)
    {
        return $this->value->startOfWeek($weekStartsAt);
    }

    public function endOfWeek($weekEndsAt = null)
    {
        return $this->value->endOfWeek($weekEndsAt);
    }

    public function startOfHour()
    {
        return $this->value->startOfHour();
    }

    public function endOfHour()
    {
        return $this->value->endOfHour();
    }

    public function startOfMinute()
    {
        return $this->value->startOfMinute();
    }

    public function endOfMinute()
    {
        return $this->value->endOfMinute();
    }

    public function startOfSecond()
    {
        return $this->value->startOfSecond();
    }

    public function endOfSecond()
    {
        return $this->value->endOfSecond();
    }

    public function startOf($unit, $params)
    {
        return $this->value->startOf($unit, $params);
    }

    public function endOf($unit, $params)
    {
        return $this->value->endOf($unit, $params);
    }

    public function eq($date)
    {
        return $this->value->eq($date);
    }

    public function equalTo($date)
    {
        return $this->value->equalTo($date);
    }

    public function ne($date)
    {
        return $this->value->ne($date);
    }

    public function notEqualTo($date)
    {
        return $this->value->notEqualTo($date);
    }

    public function gt($date)
    {
        return $this->value->gt($date);
    }

    public function greaterThan($date)
    {
        return $this->value->greaterThan($date);
    }

    public function isAfter($date)
    {
        return $this->value->isAfter($date);
    }

    public function gte($date)
    {
        return $this->value->gte($date);
    }

    public function greaterThanOrEqualTo($date)
    {
        return $this->value->greaterThanOrEqualTo($date);
    }

    public function lt($date)
    {
        return $this->value->lt($date);
    }

    public function lessThan($date)
    {
        return $this->value->lessThan($date);
    }

    public function isBefore($date)
    {
        return $this->value->isBefore($date);
    }

    public function lte($date)
    {
        return $this->value->lte($date);
    }

    public function lessThanOrEqualTo($date)
    {
        return $this->value->lessThanOrEqualTo($date);
    }

    public function between($date1, $date2, $equal = 1)
    {
        return $this->value->between($date1, $date2, $equal);
    }

    public function betweenIncluded($date1, $date2)
    {
        return $this->value->betweenIncluded($date1, $date2);
    }

    public function betweenExcluded($date1, $date2)
    {
        return $this->value->betweenExcluded($date1, $date2);
    }

    public function isBetween($date1, $date2, $equal = 1)
    {
        return $this->value->isBetween($date1, $date2, $equal);
    }

    public function isWeekday()
    {
        return $this->value->isWeekday();
    }

    public function isWeekend()
    {
        return $this->value->isWeekend();
    }

    public function isYesterday()
    {
        return $this->value->isYesterday();
    }

    public function isToday()
    {
        return $this->value->isToday();
    }

    public function isTomorrow()
    {
        return $this->value->isTomorrow();
    }

    public function isFuture()
    {
        return $this->value->isFuture();
    }

    public function isPast()
    {
        return $this->value->isPast();
    }

    public function isLeapYear()
    {
        return $this->value->isLeapYear();
    }

    public function isLongYear()
    {
        return $this->value->isLongYear();
    }

    public function isSameAs($format, $date = null)
    {
        return $this->value->isSameAs($format, $date);
    }

    public function isSameUnit($unit, $date = null)
    {
        return $this->value->isSameUnit($unit, $date);
    }

    public function isCurrentUnit($unit)
    {
        return $this->value->isCurrentUnit($unit);
    }

    public function isSameQuarter($date = null, $ofSameYear = 1)
    {
        return $this->value->isSameQuarter($date, $ofSameYear);
    }

    public function isSameMonth($date = null, $ofSameYear = 1)
    {
        return $this->value->isSameMonth($date, $ofSameYear);
    }

    public function isDayOfWeek($dayOfWeek)
    {
        return $this->value->isDayOfWeek($dayOfWeek);
    }

    public function isBirthday($date = null)
    {
        return $this->value->isBirthday($date);
    }

    public function isLastOfMonth()
    {
        return $this->value->isLastOfMonth();
    }

    public function isStartOfDay($checkMicroseconds = null)
    {
        return $this->value->isStartOfDay($checkMicroseconds);
    }

    public function isEndOfDay($checkMicroseconds = null)
    {
        return $this->value->isEndOfDay($checkMicroseconds);
    }

    public function isMidnight()
    {
        return $this->value->isMidnight();
    }

    public function isMidday()
    {
        return $this->value->isMidday();
    }

    public function is($tester)
    {
        return $this->value->is($tester);
    }

    public function isStartOfTime()
    {
        return $this->value->isStartOfTime();
    }

    public function isEndOfTime()
    {
        return $this->value->isEndOfTime();
    }

    public function rawFormat($format)
    {
        return $this->value->rawFormat($format);
    }

    public function toDateString()
    {
        return $this->value->toDateString();
    }

    public function toFormattedDateString()
    {
        return $this->value->toFormattedDateString();
    }

    public function toTimeString($unitPrecision = 'second')
    {
        return $this->value->toTimeString($unitPrecision);
    }

    public function toDateTimeString($unitPrecision = 'second')
    {
        return $this->value->toDateTimeString($unitPrecision);
    }

    public function toDateTimeLocalString($unitPrecision = 'second')
    {
        return $this->value->toDateTimeLocalString($unitPrecision);
    }

    public function toDayDateTimeString()
    {
        return $this->value->toDayDateTimeString();
    }

    public function toAtomString()
    {
        return $this->value->toAtomString();
    }

    public function toCookieString()
    {
        return $this->value->toCookieString();
    }

    public function toIso8601String()
    {
        return $this->value->toIso8601String();
    }

    public function toRfc822String()
    {
        return $this->value->toRfc822String();
    }

    public function toIso8601ZuluString($unitPrecision = 'second')
    {
        return $this->value->toIso8601ZuluString($unitPrecision);
    }

    public function toRfc850String()
    {
        return $this->value->toRfc850String();
    }

    public function toRfc1036String()
    {
        return $this->value->toRfc1036String();
    }

    public function toRfc1123String()
    {
        return $this->value->toRfc1123String();
    }

    public function toRfc2822String()
    {
        return $this->value->toRfc2822String();
    }

    public function toRfc3339String($extended = null)
    {
        return $this->value->toRfc3339String($extended);
    }

    public function toRssString()
    {
        return $this->value->toRssString();
    }

    public function toW3cString()
    {
        return $this->value->toW3cString();
    }

    public function toRfc7231String()
    {
        return $this->value->toRfc7231String();
    }

    public function toArray()
    {
        return $this->value->toArray();
    }

    public function toObject()
    {
        return $this->value->toObject();
    }

    public function toString()
    {
        return $this->value->toString();
    }

    public function toISOString($keepOffset = null)
    {
        return $this->value->toISOString($keepOffset);
    }

    public function toJSON()
    {
        return $this->value->toJSON();
    }

    public function toDateTime()
    {
        return $this->value->toDateTime();
    }

    public function toDateTimeImmutable()
    {
        return $this->value->toDateTimeImmutable();
    }

    public function toDate()
    {
        return $this->value->toDate();
    }

    public function toPeriod($end = null, $interval = null, $unit = null)
    {
        return $this->value->toPeriod($end, $interval, $unit);
    }

    public function range($end = null, $interval = null, $unit = null)
    {
        return $this->value->range($end, $interval, $unit);
    }

    public function diffAsCarbonInterval($date = null, $absolute = 1)
    {
        return $this->value->diffAsCarbonInterval($date, $absolute);
    }

    public function diffInYears($date = null, $absolute = 1)
    {
        return $this->value->diffInYears($date, $absolute);
    }

    public function diffInQuarters($date = null, $absolute = 1)
    {
        return $this->value->diffInQuarters($date, $absolute);
    }

    public function diffInMonths($date = null, $absolute = 1)
    {
        return $this->value->diffInMonths($date, $absolute);
    }

    public function diffInWeeks($date = null, $absolute = 1)
    {
        return $this->value->diffInWeeks($date, $absolute);
    }

    public function diffInDays($date = null, $absolute = 1)
    {
        return $this->value->diffInDays($date, $absolute);
    }

    public function diffInDaysFiltered($callback, $date = null, $absolute = 1)
    {
        return $this->value->diffInDaysFiltered($callback, $date, $absolute);
    }

    public function diffInHoursFiltered($callback, $date = null, $absolute = 1)
    {
        return $this->value->diffInHoursFiltered($callback, $date, $absolute);
    }

    public function diffFiltered($ci, $callback, $date = null, $absolute = 1)
    {
        return $this->value->diffFiltered($ci, $callback, $date, $absolute);
    }

    public function diffInWeekdays($date = null, $absolute = 1)
    {
        return $this->value->diffInWeekdays($date, $absolute);
    }

    public function diffInWeekendDays($date = null, $absolute = 1)
    {
        return $this->value->diffInWeekendDays($date, $absolute);
    }

    public function diffInHours($date = null, $absolute = 1)
    {
        return $this->value->diffInHours($date, $absolute);
    }

    public function diffInRealHours($date = null, $absolute = 1)
    {
        return $this->value->diffInRealHours($date, $absolute);
    }

    public function diffInMinutes($date = null, $absolute = 1)
    {
        return $this->value->diffInMinutes($date, $absolute);
    }

    public function diffInRealMinutes($date = null, $absolute = 1)
    {
        return $this->value->diffInRealMinutes($date, $absolute);
    }

    public function diffInSeconds($date = null, $absolute = 1)
    {
        return $this->value->diffInSeconds($date, $absolute);
    }

    public function diffInMicroseconds($date = null, $absolute = 1)
    {
        return $this->value->diffInMicroseconds($date, $absolute);
    }

    public function diffInMilliseconds($date = null, $absolute = 1)
    {
        return $this->value->diffInMilliseconds($date, $absolute);
    }

    public function diffInRealSeconds($date = null, $absolute = 1)
    {
        return $this->value->diffInRealSeconds($date, $absolute);
    }

    public function diffInRealMicroseconds($date = null, $absolute = 1)
    {
        return $this->value->diffInRealMicroseconds($date, $absolute);
    }

    public function diffInRealMilliseconds($date = null, $absolute = 1)
    {
        return $this->value->diffInRealMilliseconds($date, $absolute);
    }

    public function floatDiffInSeconds($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInSeconds($date, $absolute);
    }

    public function floatDiffInMinutes($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInMinutes($date, $absolute);
    }

    public function floatDiffInHours($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInHours($date, $absolute);
    }

    public function floatDiffInDays($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInDays($date, $absolute);
    }

    public function floatDiffInWeeks($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInWeeks($date, $absolute);
    }

    public function floatDiffInMonths($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInMonths($date, $absolute);
    }

    public function floatDiffInYears($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInYears($date, $absolute);
    }

    public function floatDiffInRealSeconds($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInRealSeconds($date, $absolute);
    }

    public function floatDiffInRealMinutes($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInRealMinutes($date, $absolute);
    }

    public function floatDiffInRealHours($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInRealHours($date, $absolute);
    }

    public function floatDiffInRealDays($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInRealDays($date, $absolute);
    }

    public function floatDiffInRealWeeks($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInRealWeeks($date, $absolute);
    }

    public function floatDiffInRealMonths($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInRealMonths($date, $absolute);
    }

    public function floatDiffInRealYears($date = null, $absolute = 1)
    {
        return $this->value->floatDiffInRealYears($date, $absolute);
    }

    public function secondsSinceMidnight()
    {
        return $this->value->secondsSinceMidnight();
    }

    public function secondsUntilEndOfDay()
    {
        return $this->value->secondsUntilEndOfDay();
    }

    public function diffForHumans($other = null, $syntax = null, $short = null, $parts = 1, $options = null)
    {
        return $this->value->diffForHumans($other, $syntax, $short, $parts, $options);
    }

    public function from($other = null, $syntax = null, $short = null, $parts = 1, $options = null)
    {
        return $this->value->from($other, $syntax, $short, $parts, $options);
    }

    public function since($other = null, $syntax = null, $short = null, $parts = 1, $options = null)
    {
        return $this->value->since($other, $syntax, $short, $parts, $options);
    }

    public function to($other = null, $syntax = null, $short = null, $parts = 1, $options = null)
    {
        return $this->value->to($other, $syntax, $short, $parts, $options);
    }

    public function until($other = null, $syntax = null, $short = null, $parts = 1, $options = null)
    {
        return $this->value->until($other, $syntax, $short, $parts, $options);
    }

    public function fromNow($syntax = null, $short = null, $parts = 1, $options = null)
    {
        return $this->value->fromNow($syntax, $short, $parts, $options);
    }

    public function toNow($syntax = null, $short = null, $parts = 1, $options = null)
    {
        return $this->value->toNow($syntax, $short, $parts, $options);
    }

    public function ago($syntax = null, $short = null, $parts = 1, $options = null)
    {
        return $this->value->ago($syntax, $short, $parts, $options);
    }

    public function timespan($other = null, $timezone = null)
    {
        return $this->value->timespan($other, $timezone);
    }

    public function calendar($referenceTime = null, $formats = [])
    {
        return $this->value->calendar($referenceTime, $formats);
    }

    public function hasLocalMacro($name)
    {
        return $this->value->hasLocalMacro($name);
    }

    public function getLocalMacro($name)
    {
        return $this->value->getLocalMacro($name);
    }

    public function midDay()
    {
        return $this->value->midDay();
    }

    public function next($modifier = null)
    {
        return $this->value->next($modifier);
    }

    public function nextWeekday()
    {
        return $this->value->nextWeekday();
    }

    public function previousWeekday()
    {
        return $this->value->previousWeekday();
    }

    public function nextWeekendDay()
    {
        return $this->value->nextWeekendDay();
    }

    public function previousWeekendDay()
    {
        return $this->value->previousWeekendDay();
    }

    public function previous($modifier = null)
    {
        return $this->value->previous($modifier);
    }

    public function firstOfMonth($dayOfWeek = null)
    {
        return $this->value->firstOfMonth($dayOfWeek);
    }

    public function lastOfMonth($dayOfWeek = null)
    {
        return $this->value->lastOfMonth($dayOfWeek);
    }

    public function nthOfMonth($nth, $dayOfWeek)
    {
        return $this->value->nthOfMonth($nth, $dayOfWeek);
    }

    public function firstOfQuarter($dayOfWeek = null)
    {
        return $this->value->firstOfQuarter($dayOfWeek);
    }

    public function lastOfQuarter($dayOfWeek = null)
    {
        return $this->value->lastOfQuarter($dayOfWeek);
    }

    public function nthOfQuarter($nth, $dayOfWeek)
    {
        return $this->value->nthOfQuarter($nth, $dayOfWeek);
    }

    public function firstOfYear($dayOfWeek = null)
    {
        return $this->value->firstOfYear($dayOfWeek);
    }

    public function lastOfYear($dayOfWeek = null)
    {
        return $this->value->lastOfYear($dayOfWeek);
    }

    public function nthOfYear($nth, $dayOfWeek)
    {
        return $this->value->nthOfYear($nth, $dayOfWeek);
    }

    public function average($date = null)
    {
        return $this->value->average($date);
    }

    public function closest($date1, $date2)
    {
        return $this->value->closest($date1, $date2);
    }

    public function farthest($date1, $date2)
    {
        return $this->value->farthest($date1, $date2);
    }

    public function min($date = null)
    {
        return $this->value->min($date);
    }

    public function minimum($date = null)
    {
        return $this->value->minimum($date);
    }

    public function max($date = null)
    {
        return $this->value->max($date);
    }

    public function maximum($date = null)
    {
        return $this->value->maximum($date);
    }

    public function change($modifier)
    {
        return $this->value->change($modifier);
    }

    public function toMutable()
    {
        return $this->value->toMutable();
    }

    public function toImmutable()
    {
        return $this->value->toImmutable();
    }

    public function cast($className)
    {
        return $this->value->cast($className);
    }

    public function settings($settings)
    {
        return $this->value->settings($settings);
    }

    public function getSettings()
    {
        return $this->value->getSettings();
    }

    public function hasLocalTranslator()
    {
        return $this->value->hasLocalTranslator();
    }

    public function getLocalTranslator()
    {
        return $this->value->getLocalTranslator();
    }

    public function setLocalTranslator($translator)
    {
        return $this->value->setLocalTranslator($translator);
    }

    public function getTranslationMessage($key, $locale = null, $default = null, $translator = null)
    {
        return $this->value->getTranslationMessage($key, $locale, $default, $translator);
    }

    public function translate($key, $parameters = [], $number = null, $translator = null, $altNumbers = null)
    {
        return $this->value->translate($key, $parameters, $number, $translator, $altNumbers);
    }

    public function translateNumber($number)
    {
        return $this->value->translateNumber($number);
    }

    public function translateTimeStringTo($timeString, $to = null)
    {
        return $this->value->translateTimeStringTo($timeString, $to);
    }

    public function locale(?string $locale = null, $fallbackLocales)
    {
        return $this->value->locale($locale, $fallbackLocales);
    }

    public function roundUnit($unit, $precision = 1, $function = 'round')
    {
        return $this->value->roundUnit($unit, $precision, $function);
    }

    public function floorUnit($unit, $precision = 1)
    {
        return $this->value->floorUnit($unit, $precision);
    }

    public function ceilUnit($unit, $precision = 1)
    {
        return $this->value->ceilUnit($unit, $precision);
    }

    public function round($precision = 1, $function = 'round')
    {
        return $this->value->round($precision, $function);
    }

    public function floor($precision = 1)
    {
        return $this->value->floor($precision);
    }

    public function ceil($precision = 1)
    {
        return $this->value->ceil($precision);
    }

    public function roundWeek($weekStartsAt = null)
    {
        return $this->value->roundWeek($weekStartsAt);
    }

    public function floorWeek($weekStartsAt = null)
    {
        return $this->value->floorWeek($weekStartsAt);
    }

    public function ceilWeek($weekStartsAt = null)
    {
        return $this->value->ceilWeek($weekStartsAt);
    }

    public function serialize()
    {
        return $this->value->serialize();
    }

    public function jsonSerialize()
    {
        return $this->value->jsonSerialize();
    }

    public function cleanupDumpProperties()
    {
        return $this->value->cleanupDumpProperties();
    }

    public function timestamp($unixTimestamp)
    {
        return $this->value->timestamp($unixTimestamp);
    }

    public function getPreciseTimestamp($precision = 6)
    {
        return $this->value->getPreciseTimestamp($precision);
    }

    public function valueOf()
    {
        return $this->value->valueOf();
    }

    public function unix()
    {
        return $this->value->unix();
    }

    public function addRealUnit($unit, $value = 1)
    {
        return $this->value->addRealUnit($unit, $value);
    }

    public function subRealUnit($unit, $value = 1)
    {
        return $this->value->subRealUnit($unit, $value);
    }

    public function rawAdd($interval)
    {
        return $this->value->rawAdd($interval);
    }

    public function addUnit($unit, $value = 1, $overflow = null)
    {
        return $this->value->addUnit($unit, $value, $overflow);
    }

    public function subUnit($unit, $value = 1, $overflow = null)
    {
        return $this->value->subUnit($unit, $value, $overflow);
    }

    public function rawSub($interval)
    {
        return $this->value->rawSub($interval);
    }

    public function subtract($unit, $value = 1, $overflow = null)
    {
        return $this->value->subtract($unit, $value, $overflow);
    }

    public function isoWeekYear($year = null, $dayOfWeek = null, $dayOfYear = null)
    {
        return $this->value->isoWeekYear($year, $dayOfWeek, $dayOfYear);
    }

    public function weekYear($year = null, $dayOfWeek = null, $dayOfYear = null)
    {
        return $this->value->weekYear($year, $dayOfWeek, $dayOfYear);
    }

    public function isoWeeksInYear($dayOfWeek = null, $dayOfYear = null)
    {
        return $this->value->isoWeeksInYear($dayOfWeek, $dayOfYear);
    }

    public function weeksInYear($dayOfWeek = null, $dayOfYear = null)
    {
        return $this->value->weeksInYear($dayOfWeek, $dayOfYear);
    }

    public function week($week = null, $dayOfWeek = null, $dayOfYear = null)
    {
        return $this->value->week($week, $dayOfWeek, $dayOfYear);
    }

    public function isoWeek($week = null, $dayOfWeek = null, $dayOfYear = null)
    {
        return $this->value->isoWeek($week, $dayOfWeek, $dayOfYear);
    }

    public function __call($method, $parameters)
    {
        if (array_key_exists($method, $this->forwardDynamicCalls)) {
            return call_user_func_array([$this->value, $method], $parameters);
        }

        if (! static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.', static::class, $method
            ));
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }
}
