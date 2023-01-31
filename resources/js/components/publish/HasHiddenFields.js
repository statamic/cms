import Values from './Values.js';

export default {

    computed: {

        hiddenFields() {
            return this.$store.state.publish[this.publishContainer].hiddenFields;
        },

        jsonSubmittingFields() {
            return this.$store.state.publish[this.publishContainer].jsonSubmittingFields;
        },

        revealerFields() {
            return this.$store.state.publish[this.publishContainer].revealerFields;
        },

        visibleValues() {
            let omittableFields = _.chain(this.hiddenFields)
                .pick(field => field.omitValue)
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

            preserveFields.forEach(dottedKey => {
                newValues.set(dottedKey, originalValues.get(dottedKey));
            });

            return newValues.all();
        },

    },

}
