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
            var dottedFieldPath = dottedKey || field.handle;
            var dottedPrefix = dottedKey ? dottedKey.replace(new RegExp('\.'+field.handle+'$'), '') : '';

            // If we know the field is to permanently hidden, bypass validation.
            if (field.visibility === 'hidden' || this.shouldForceHiddenField(dottedFieldPath)) {
                this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                    dottedKey: dottedFieldPath,
                    hidden: 'force',
                    omitValue: false,
                });

                return false;
            }

            // Use validation to determine whether field should be shown.
            var validator = new Validator(field, this.values, this.$store, this.storeName);
            var passes = validator.passesConditions();

            // Ensure DOM is updated to ensure all revealers are properly loaded and tracked before committing to store.
            this.$nextTick(() => {
                var hasRevealerCondition = validator.hasRevealerCondition(dottedPrefix);

                this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                    dottedKey: dottedFieldPath,
                    hidden: ! passes,
                    omitValue: (! passes) && (! hasRevealerCondition),
                });
            });

            return passes;
        },

        shouldForceHiddenField(dottedFieldPath) {
            return data_get(this.$store.state.publish[this.storeName].hiddenFields[dottedFieldPath], 'hidden') === 'force';
        },
    }
}
