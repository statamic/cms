export default {
    methods: {
        normalizeInputOptions(options) {
            return _.map(options, (value, key) => {
                return {
                    'value': Array.isArray(options) ? value : key,
                    'label': value || key
                };
            });
        }
    }
}
