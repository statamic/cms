import Validator from './Validator.js';

export default {
    inject: {
        storeName: {
            default: 'base'
        }
    },

    methods: {
        showField(field, dottedKey) {
            var dottedPrefix = dottedKey
                ? dottedKey.replace(new RegExp('\.'+field.handle+'$'), '')
                : '';

            if (field.visibility === 'hidden') {
                var hideField = true;
                var omitValue = false;
            } else {
                var validator = new Validator(field, this.values, this.$store, this.storeName);
                var hideField = ! validator.passesConditions();
                var omitValue = ! validator.hasRevealerCondition(dottedPrefix);
            }

            this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                dottedKey: dottedKey || field.handle,
                hidden: hideField,
                omitValue: hideField && omitValue,
            });

            return ! hideField;
        }
    }
}
