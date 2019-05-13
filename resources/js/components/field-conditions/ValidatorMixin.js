import Validator from './Validator.js';

export default {
    inject: {
        storeName: {
            default: 'base'
        }
    },

    methods: {
        showField(field) {
            let validator = new Validator(field, this.values, this.$store, this.storeName);

            return validator.passesConditions();
        }
    }
}
