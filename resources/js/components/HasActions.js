export default {

    computed: {

        actions() {
            return this.$actions
                .get(this.$options.name)
                .filter(action => action.visible());;
        },

        hasActions() {
            return this.$options.name !== null && this.actions.length > 0;
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
