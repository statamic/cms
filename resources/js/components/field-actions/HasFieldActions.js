export default {

    computed: {

        fieldActions() {
            return this.$fieldActions.get(this.$options.name);
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

        visibleInternalFieldActions() {
            return this.internalFieldActions
                .filter(action => {
                    if (typeof action.visible === 'function') return action.visible(this.fieldActionPayload);
                    if (typeof action.visible !== 'undefined') return action.visible;
                    return true;
                });
        },

        visibleQuickFieldActions() {
            return [
                ...this.visibleFieldActions,
                ...this.visibleInternalFieldActions
            ].filter(item => item.quick);
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
