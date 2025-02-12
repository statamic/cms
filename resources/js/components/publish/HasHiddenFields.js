import Values from './Values.js';

export default {
    computed: {
        hiddenFields() {
            return this.store.hiddenFields;
        },

        jsonSubmittingFields() {
            return this.store.jsonSubmittingFields;
        },

        revealerFields() {
            return this.store.revealerFields;
        },

        visibleValues() {
            let omittableFields = _.chain(this.hiddenFields)
                .pick((field) => field.omitValue)
                .keys()
                .value();

            return new Values(this.values, this.jsonSubmittingFields).except(omittableFields);
        },
    },

    methods: {
        resetValuesFromResponse(responseValues) {
            if (!responseValues) return this.values;

            let preserveFields = ['id'].concat(this.revealerFields);
            let originalValues = new Values(this.values, this.jsonSubmittingFields);
            let newValues = new Values(responseValues, this.jsonSubmittingFields);

            newValues.mergeDottedKeys(preserveFields, originalValues);

            return newValues.all();
        },
    },
};
