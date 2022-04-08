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
                    let parent = this.dottedKeyToParentHandle(dottedKey);

                    if (this.parentFieldSubmitsJson(parent)) {
                        return visibleValues[parent] = this.forgetFromChildJson(visibleValues[parent], dottedKey);
                    }

                    eval('delete visibleValues.' + this.dottedKeyToJsProperty(dottedKey));
                });

            return visibleValues;
        },

    },

    methods: {

        dottedKeyToJsProperty(dottedKey) {
            return dottedKey.replace(/\.*(\d+)\./g, '[$1].');
        },

        dottedKeyToParentHandle(dottedKey) {
            return dottedKey.replace(/\..*$/, '');
        },

        dottedKeyToChildHandle(dottedKey) {
            return dottedKey.replace(/^[^\.]*\./, '');
        },

        getParentFieldType(handle) {
            let fields = [];

            this.$store.state.publish[this.publishContainer].blueprint.sections.forEach(section => {
                fields = fields.concat(section.fields);
            });

            let parentFields = _.object(_.map(fields, function(field) {
               return [field.handle, field];
            }));

            return parentFields[handle].type;
        },

        parentFieldSubmitsJson(handle) {
            return this.getParentFieldType(handle) === 'bard';
        },

        forgetFromChildJson(json, dottedKey) {
            let updatedJsonObject = JSON.parse(json);

            let childKey = this.dottedKeyToChildHandle(dottedKey);

            eval('delete updatedJsonObject' + this.dottedKeyToJsProperty(childKey));

            return JSON.stringify(updatedJsonObject);
        },

    },

}
