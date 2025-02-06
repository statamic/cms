import FieldAction from './FieldAction';

export default {
    computed: {
        fieldActions() {
            return [...this.$fieldActions.get(this.$options.name), ...this.internalFieldActions]
                .map((action) => new FieldAction(action, this.fieldActionPayload))
                .filter((action) => action.visible);
        },

        internalFieldActions() {
            return [];
        },

        fieldActionPayload() {
            return {};
        },
    },
};
