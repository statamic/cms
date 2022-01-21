import Validator from './Validator.js';

export default {
    inject: {
        storeName: {
            default: 'base'
        }
    },

    methods: {
        showField(field) {
            let passes = new Validator(field, this.values, this.$store, this.storeName).passesConditions();

            this.$store.commit(`publish/${this.storeName}/setHiddenField`, {
                handle: field.handle,
                hidden: ! passes,
            });

            return passes;
        }
    }
}
