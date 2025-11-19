import { translate as __ } from './translate';

export default function normalizeInputOptions(options) {
    if (!Array.isArray(options)) {
        return Object.entries(options).map(([key, value]) => {
            return {
                value: Array.isArray(options) ? value : key,
                label: `${__(value) || key}`,
            };
        });
    }

    return options.map((option) => {
        if (typeof option === 'object') {
            let valueKey = 'value';
            let labelKey = 'label';

            // Support both {key: '', value: ''} and {value: '', label: ''} formats.
            if (option.hasOwnProperty('key')) {
                valueKey = 'key';
                labelKey = 'value';
            }

            return {
                value: option[valueKey],
                label: `${__(option[labelKey]) || option[valueKey]}`,
            };
        }

        return {
            value: option,
            label: `${__(option) || option}`,
        };
    });
}
