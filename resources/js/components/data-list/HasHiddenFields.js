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
            let omittableFields = _.chain(this.hiddenFields)
                .pick(field => field.omitValue)
                .keys()
                .value();

            return new HiddenValuesOmitter(this.values, this.jsonSubmittingFields).omit(omittableFields);
        },

    }

}
