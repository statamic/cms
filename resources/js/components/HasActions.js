export default {

    computed: {

        actions() {
            return this.$actions.get(this.$options.name);
        },

        hasActions() {
            return this.$options.name !== null && this.actions.length > 0;
        },

        actionPayload() { 
            return {};
        },
         
    },

    methods: {

        runAction({ callback }) {
            callback({
                ...this.actionPayload,
            });
        },

    }

}
