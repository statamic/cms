<template>

    <div>

        <tabs
            :initial-tabs="tabs"
            :require-section="config.require_set"
            :can-define-localizable="false"
            :add-tab-text="__('Add Set Group')"
            :edit-tab-text="__('Edit Set Group')"
            :new-tab-text="__('New Set Group')"
            :add-section-text="__('Add Set')"
            :edit-section-text="__('Edit Set')"
            :new-section-text="__('New Set')"
            show-tab-instructions-field
            show-section-handle-field
            @updated="tabsUpdated"
        />

    </div>

</template>

<script>
import Tabs from '../../blueprints/Tabs.vue';

export default {

    mixins: [Fieldtype],

    components: {
        Tabs
    },

    provide() {
        return {
            suggestableConditionFieldsProvider: this.makeConditionsProvider(),
        }
    },

    data() {
        return {
            tabs: this.value
        }
    },

    computed: {

        suggestableConditionFields() {
            let fields = this.tabs.reduce((fields, tab) => {
                return fields.concat(tab.sections.reduce((fields, section) => {
                    let sectionFields = section.fields.reduce((fields, field) => {
                        return fields.concat(
                            field.type === 'import'
                                ? this.getFieldsFromImportedFieldset(field.fieldset, field.prefix)
                                : [field.handle]
                        );
                    }, []);
                    return fields.concat(sectionFields);
                }, []));
            }, []);

            return _.unique(fields);
        }

    },

    methods: {

        tabsUpdated(tabs) {
            this.update(tabs);
        },

        makeConditionsProvider() {
            const provide = {};
            Object.defineProperties(provide, {
                suggestableFields: { get: () => this.suggestableConditionFields },
            });
            return provide;
        },

    }

}
</script>
