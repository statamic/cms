export default {

    provide() {
        return {
            suggestableConditionFieldsProvider: this.makeConditionsProvider(),
        }
    },

    computed: {

        fieldsForConditionSuggestions() {
            return this.tabs.reduce((fields, tab) => {
                return fields.concat(tab.sections.reduce((fields, section) => {
                    return fields.concat(section.fields);
                }, []));
            }, []);
        },

        suggestableConditionFields() {
            let fields = this.fieldsForConditionSuggestions.reduce((fields, field) => {
                return fields.concat(
                    field.type === 'import'
                        ? this.getFieldsFromImportedFieldset(field.fieldset, field.prefix)
                        : [field.handle]
                );
            }, []);

            return _.unique(fields);
        },

    },

    methods: {

        makeConditionsProvider() {
            const provide = {};
            Object.defineProperties(provide, {
                suggestableFields: { get: () => this.suggestableConditionFields },
            });
            return provide;
        },

        getFieldsFromImportedFieldset(fieldset, prefix) {
            return Statamic.$config.get(`fieldsets.${fieldset}.fields`, [])
                .reduce((fields, field) => {
                    return fields.concat(
                        field.type === 'import'
                            ? this.getFieldsFromImportedFieldset(field.fieldset, field.prefix)
                            : [field.handle]
                    );
                }, [])
                .map(handle => prefix ? `${prefix}${handle}` : handle);
        }

    }

}
