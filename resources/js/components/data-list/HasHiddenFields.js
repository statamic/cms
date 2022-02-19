export default {

    computed: {

        hiddenFields() {
            return this.$store.state.publish[this.publishContainer].hiddenFields;
        },

        visibleValues() {
            return _.omit(this.values, (_, handle) => {
                return this.hiddenFields[handle];
            });
        },

    }

}
