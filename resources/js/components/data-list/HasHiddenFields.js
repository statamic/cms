import HiddenValuesOmitter from '../field-conditions/Omitter.js';

export default {

    computed: {

        hiddenFields() {
            return this.$store.state.publish[this.publishContainer].hiddenFields;
        },

        jsonSubmittingFields() {
            return this.$store.state.publish[this.publishContainer].jsonSubmittingFields;
        },

        visibleValues() {
            let hiddenFields = _.chain(this.hiddenFields)
                .pick(field => field.hidden && field.omitValue)
                .keys()
                .value();

            return new HiddenValuesOmitter(this.values, this.jsonSubmittingFields).omit(hiddenFields);
        },

    }

}
