import Validator from './Validator.js';
import { data_get } from  '../../bootstrap/globals.js'

export default {
    inject: {
        storeName: {
            default: 'base'
        }
    },

    methods: {
        showField(field, dottedKey) {
            let dottedFieldPath = dottedKey || field.handle;
            let dottedPrefix = dottedKey? dottedKey.replace(new RegExp('\.'+field.handle+'$'), '') : '';

            if (this.shouldForceHiddenField(dottedFieldPath)) {
                return false;
            }

            if (field.visibility === 'hidden') {
                var hideField = true;
                var omitValue = false;
            } else {
                var validator = new Validator(field, this.values, this.$store, this.storeName);
                var hideField = ! validator.passesConditions();
                var omitValue = ! validator.hasRevealerCondition(dottedPrefix);
            }

            this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                dottedKey: dottedFieldPath,
                hidden: hideField,
                omitValue: hideField && omitValue,
            });

            return ! hideField;
        },

        shouldForceHiddenField(dottedFieldPath) {
            return data_get(this.$store.state.publish[this.storeName].hiddenFields[dottedFieldPath], 'hidden') === 'force';
        },
    }
}
