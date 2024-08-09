export default {
    methods: {
        normalizeInputOptions(options) {
            if (! Array.isArray(options)) {
                return _.map(options, (value, key) => {
                    return {
                        'value': Array.isArray(options) ? value : key,
                        'label': __(value) || key
                    };
                });
            }

            return _.map(options, (option) => {
                if (typeof option === 'object') {
                    return {
                        'value': option.value,
                        'label': __(option.label) || option.value
                    };
                }

                return {
                    'value': option,
                    'label': __(option) || option
                };
            });
        }
    }
}
