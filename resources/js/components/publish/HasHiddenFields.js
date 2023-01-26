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
            let preserveFields = ['id'].concat(this.revealerFields);
            let preservedValues = new Values(this.values, this.jsonSubmittingFields).only(preserveFields);
            let mergedValues = new Values(responseValues, this.jsonSubmittingFields).merge(preservedValues);

            return mergedValues;
        },

    },

}
