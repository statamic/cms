export default {

    computed: {

        hiddenFields() {
            return this.$store.state.publish[this.publishContainer].hiddenFields;
        },

        visibleValues() {
            let visibleValues = clone(this.values);

            let hiddenKeys = _.chain(this.hiddenFields)
                .pick(hidden => hidden)
                .keys()
                .each(dottedKey => {
                    eval('delete visibleValues.' + this.dottedKeyToJsProperty(dottedKey));
                });

            return visibleValues;
        },

    },

    methods: {

        dottedKeyToJsProperty(dottedKey) {
            return dottedKey.replace(/\.*(\d+)\./g, '[$1].');
        },

    },

}
