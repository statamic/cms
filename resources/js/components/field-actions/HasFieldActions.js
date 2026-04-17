import toFieldActions from './toFieldActions.js';

export default {
    data() {
        return {
            _cachedFieldActions: null,
        };
    },

    computed: {
        fieldActions() {
            if (this._cachedFieldActions) return this._cachedFieldActions;
            this._cachedFieldActions = toFieldActions(
                this.fieldActionBinding,
                this.fieldActionPayload,
                this.internalFieldActions,
            );
            return this._cachedFieldActions;
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
