import { __ } from '../../bootstrap/globals.js';

export default {
    methods: {
        normalizeInputOptions(options) {
            if (!Array.isArray(options)) {
                return _.map(options, (value, key) => {
                    return {
                        value: Array.isArray(options) ? value : key,
                        label: __(value) || key,
                    };
                });
            }

            return _.map(options, (option) => {
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
                        label: __(option[labelKey]) || option[valueKey],
                    };
                }

                return {
                    value: option,
                    label: __(option) || option,
                };
            });
        },
    },
};
