import { config } from '@api';
import { getLocalTimeZone } from '@internationalized/date';

export function getAdditionalTimezones(timeZone) {
    if (!timeZone) return [];
    if (timeZone === (config.get('displayTimezone') ?? 'UTC')) return [];
    if (timeZone === getLocalTimeZone()) return [];
    return [{ timezone: timeZone, label: __('This field') }];
}
