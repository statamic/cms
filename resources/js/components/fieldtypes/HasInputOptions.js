import { __ } from '../../bootstrap/globals.js';

export default {
    methods: {
        normalizeInputOptions(options) {
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

                    // Support {key: '', value: ''}, {key: '', label: ''}, and {value: '', label: ''} formats.
                    if (option.hasOwnProperty('key')) {
                        valueKey = 'key';
                        labelKey = option.hasOwnProperty('value') ? 'value' : 'label';
                    }

                    return {
                        value: option[valueKey],
                        label: `${__(option[labelKey]) || option[valueKey]}`,
                        ...(option.icon ? { icon: option.icon } : {}),
                    };
                }

                return {
                    value: option,
                    label: `${__(option) || option}`,
                };
            });
        },
    },
};
