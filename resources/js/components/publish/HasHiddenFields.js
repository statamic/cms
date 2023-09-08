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

            // Prosemirror's JSON will include spaces between tags.
            // For example (this is not the actual json)...
            // "<p>One <b>two</b> three</p>" becomes ['OneSPACE', '<b>two</b>', 'SPACEthree']
            // But, Laravel's TrimStrings middleware would remove them.
            // Those spaces need to be there, otherwise it would be rendered as <p>One<b>two</b>three</p>
            // To combat this, we submit the JSON string instead of an object.

            return new Values(this.values, this.jsonSubmittingFields).jsonEncode().except(omittableFields);
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
