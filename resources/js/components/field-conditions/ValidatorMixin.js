import ShowField from './ShowField.js';

export default {
    inject: ['store'],

    methods: {
        showField(field, dottedKey) {
            return new ShowField(this.store, this.values, this.extraValues).showField(field, dottedKey);
        },
    },
};
