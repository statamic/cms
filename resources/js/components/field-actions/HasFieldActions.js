export default {

    computed: {

        fieldActions() {
            return [
                ...this.$fieldActions.get(this.$options.name),
                ...this.internalFieldActions
            ];
        },

        internalFieldActions() {
            return [];
        },

        visibleFieldActions() {
            return this.fieldActions
                .filter(action => {
                    if (typeof action.visible === 'function') return action.visible(this.fieldActionPayload);
                    if (typeof action.visible !== 'undefined') return action.visible;
                    return true;
                });
        },

        fieldActionPayload() {
            return {};
        },

    },

    methods: {

        runFieldAction(action) {
            action.run(this.fieldActionPayload);
        },

    }

}
