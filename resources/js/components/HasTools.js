export default {

    computed: {

        tools() {
            return this.$tools.get(this.$options.name);
        },

        hasTools() {
            return this.$options.name !== null && this.tools.length > 0;
        },

        toolPayload() { 
            return {};
        },
         
    },

    methods: {

        runTool({ callback }) {
            callback({
                ...this.toolPayload,
            });
        },

    }

}
