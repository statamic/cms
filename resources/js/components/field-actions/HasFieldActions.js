import toFieldActions from './toFieldActions.js';

export default {
    computed: {
        fieldActions() {
            return toFieldActions(
                this.fieldActionBinding,
                this.fieldActionPayload,
                this.internalFieldActions,
            );
        },

        internalFieldActions() {
            return [];
        },

        fieldActionPayload() {
            return {};
        },

        fieldActionBinding() {
            return this.config.type + '-fieldtype';
        }
    },
};
