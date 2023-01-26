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

        mergeIntoValues(responseValues) {
            let newValues = this.rejectRevealerValues(responseValues);

            let mergedValues = new Values(this.values, this.jsonSubmittingFields).merge(newValues);

            return mergedValues;
        },

        rejectRevealerValues(values) {
            return new Values(values, this.jsonSubmittingFields).except(this.revealerFields);
        },

    },

}
