import Validator from './Validator.js';

export default {
    inject: {
        storeName: {
            default: 'base'
        }
    },

    methods: {
        showField(field, dottedKey) {
            let passes = new Validator(field, this.values, this.$store, this.storeName).passesConditions();

            this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                dottedKey: dottedKey || field.handle,
                hidden: ! passes,
            });

            return passes;
        }
    }
}
