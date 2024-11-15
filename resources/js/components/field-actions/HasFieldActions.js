import FieldAction from './FieldAction';

export default {

    computed: {

        fieldActions() {
            return [
                ...this.$fieldActions.get(this.$options.name),
                ...this.internalFieldActions
            ].map(action => new FieldAction(action, this.fieldActionPayload));
        },

        internalFieldActions() {
            return [];
        },

        visibleFieldActions() {
            return this.fieldActions.filter(action => action.visible);
        },

        fieldActionPayload() {
            return {};
        },

    }

}
