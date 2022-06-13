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

            let validator = new Validator(field, this.values, this.$store, this.storeName);
            let passes = validator.passesConditions();

            // TODO: The next tick here is necessary to fix #6018, but not sure it's the _right_ fix.
            // Something is loading differently, causing the below `hiddenByRevealerField` check
            // to fail, when the replicator is configured to collapse all sets by default 🤔
            this.$nextTick(() => {
                let hiddenByRevealerField = validator.hasRevealerCondition(dottedPrefix);

                this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                    dottedKey: dottedFieldPath,
                    hidden: ! passes,
                    omitValue: ! hiddenByRevealerField,
                });
            });

            return passes;
        },

        shouldForceHiddenField(dottedFieldPath) {
            return data_get(this.$store.state.publish[this.storeName].hiddenFields[dottedFieldPath], 'hidden') === 'force';
        },
    }
}
