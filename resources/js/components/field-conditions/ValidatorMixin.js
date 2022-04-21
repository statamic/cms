import Validator from './Validator.js';

export default {
    inject: {
        storeName: {
            default: 'base'
        }
    },

    methods: {
        showField(field, dottedKey) {
            let dottedPrefix = dottedKey
                ? dottedKey.replace(new RegExp('\.'+field.handle+'$'), '')
                : '';

            let validator = new Validator(field, this.values, this.$store, this.storeName);
            let passes = validator.passesConditions();
            let hiddenByRevealerField = validator.hasRevealerCondition(dottedPrefix);

            this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                dottedKey: dottedKey || field.handle,
                hidden: ! passes,
                omitValue: ! hiddenByRevealerField,
            });

            return passes;
        }
    }
}
