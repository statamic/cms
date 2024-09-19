export default {

    computed: {

        actions() {
            return this.$actions.get(this.$options.name);
        },

        internalActions() {
            return [];
        },

        visibleActions() {
            return this.actions
                .filter(action => {
                    if (typeof action.visible === 'function') return action.visible();
                    if (typeof action.visible !== 'undefined') return action.visible;
                    return true;
                });
        },

        visibleInternalActions() {
            return this.internalActions
                .filter(action => {
                    if (typeof action.visible === 'function') return action.visible();
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

        actionPayload() { 
            return {};
        },
         
    },

    methods: {

        runAction({ run }) {
            run(this.actionPayload);
        },

    }

}
