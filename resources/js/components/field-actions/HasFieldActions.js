export default {

    computed: {

        actions() {
            return this.$fieldActions.get(this.$options.name);
        },

        internalActions() {
            return [];
        },

        visibleActions() {
            return this.actions
                .filter(action => {
                    if (typeof action.visible === 'function') return action.visible(this.fieldActionPayload);
                    if (typeof action.visible !== 'undefined') return action.visible;
                    return true;
                });
        },

        visibleInternalActions() {
            return this.internalActions
                .filter(action => {
                    if (typeof action.visible === 'function') return action.visible(this.fieldActionPayload);
                    if (typeof action.visible !== 'undefined') return action.visible;
                    return true;
                });
        },

        visibleQuickActions() {
            return [
                ...this.visibleActions,
                ...this.visibleInternalActions
            ].filter(item => item.quick);
        },

        fieldActionPayload() {
            return {};
        },

    },

    methods: {

        runAction(action) {
            action.run(this.fieldActionPayload);
        },

    }

}
