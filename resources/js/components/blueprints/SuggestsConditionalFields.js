export default {
    provide() {
        return {
            suggestableConditionFieldsProvider: this.makeConditionsProvider(),
        };
    },

    computed: {
        fieldsForConditionSuggestions() {
            return this.tabs.reduce((fields, tab) => {
                return fields.concat(
                    tab.sections.reduce((fields, section) => {
                        return fields.concat(section.fields);
                    }, []),
                );
            }, []);
        },
    },

    methods: {
        suggestableConditionFields(section = null) {
            let fields = this.getSectionFieldsForConditionSuggestions(section).reduce((fields, field) => {
                return fields.concat(
                    field.type === 'import'
                        ? this.getFieldsFromImportedFieldset(field.fieldset, field.prefix)
                        : [field],
                );
            }, []);

            return [...new Set(fields)];
        },

        makeConditionsProvider() {
            const provide = {
                suggestableFields: (vm) => this.suggestableConditionFields(vm),
            };
            return provide;
        },

        getFieldsFromImportedFieldset(fieldset, prefix) {
            return Statamic.$config
                .get(`fieldsets.${fieldset}.fields`, [])
                .reduce((fields, field) => {
                    return fields.concat(
                        field.type === 'import'
                            ? this.getFieldsFromImportedFieldset(field.fieldset, field.prefix)
                            : [field],
                    );
                }, [])
                .map((field) => (prefix ? { ...field, handle: prefix + field.handle } : field));
        },

        getSectionFieldsForConditionSuggestions(vm = null) {
            return this.fieldsForConditionSuggestions;
        },
    },
};
