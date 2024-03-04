export default {

    provide() {
        return {
            suggestableConditionFieldsProvider: this.makeConditionsProvider(),
        }
    },

    methods: {

        suggestableConditionFields(section = null) {
            let fields = this.fieldsForConditionSuggestions(section).reduce((fields, field) => {
                return fields.concat(
                    field.type === 'import'
                        ? this.getFieldsFromImportedFieldset(field.fieldset, field.prefix)
                        : [field]
                );
            }, []);

            return _.unique(fields);
        },

        makeConditionsProvider() {
            const provide = {
                suggestableFields: (vm) => this.suggestableConditionFields(vm),
            };
            return provide;
        },

        getFieldsFromImportedFieldset(fieldset, prefix) {
            return Statamic.$config.get(`fieldsets.${fieldset}.fields`, [])
                .reduce((fields, field) => {
                    return fields.concat(
                        field.type === 'import'
                            ? this.getFieldsFromImportedFieldset(field.fieldset, field.prefix)
                            : [field]
                    );
                }, [])
                .map(field => prefix ? { ...field, handle: prefix + field.handle } : field);
        },

        fieldsForConditionSuggestions(vm = null) {
            return this.tabs.reduce((fields, tab) => {
                return fields.concat(tab.sections.reduce((fields, section) => {
                    return fields.concat(section.fields);
                }, []));
            }, []);
        }

    }

}
