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

            // TODO: The next tick here is necessary to fix #6018, but not sure it's the _right_ fix.
            // Something is loading differently, causing the below `hiddenByRevealerField` check
            // to fail, when the replicator is configured to collapse all sets by default 🤔
            this.$nextTick(() => {
                let hiddenByRevealerField = validator.hasRevealerCondition(dottedPrefix);

                this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                    dottedKey: dottedKey || field.handle,
                    hidden: ! passes,
                    omitValue: ! hiddenByRevealerField,
                });
            });

            return passes;
        }
    }
}
