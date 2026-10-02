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
            show-section-hide-field
            @updated="tabsUpdated"
        />
    </div>
</template>

<script>
import { computed } from 'vue';
import Fieldtype from '../Fieldtype.vue';
import SuggestsConditionalFields from '../../blueprints/SuggestsConditionalFields';
import Tabs from '../../blueprints/Tabs.vue';

export default {
    mixins: [Fieldtype, SuggestsConditionalFields],

    components: {
        Tabs,
    },

    data() {
        return {
            tabs: this.value,
        };
    },

    provide() {
        return {
            isInsideSet: true,
            setConfig: computed(() => this.meta?.setConfig),
            setConfigErrors: this.setConfigErrors,
        };
    },

    methods: {
        setConfigErrors(id) {
            for (const [tabIndex, tab] of this.value.entries()) {
                const sectionIndex = tab.sections.findIndex(section => section._id === id);
                if (sectionIndex === -1) continue;

                const prefix = [this.fieldPathPrefix, this.handle, tabIndex, 'sections', sectionIndex, 'extraConfig', 'values'].filter(part => part !== undefined && part !== '').join('.') + '.';
                return Object.fromEntries(Object.entries(this.publishContainer.errors || {})
                    .filter(([key]) => key.startsWith(prefix))
                    .map(([key, value]) => [key.slice(prefix.length), value]));
            }

            return {};
        },

        tabsUpdated(tabs) {
            this.update(tabs);
        },

        getSectionFieldsForConditionSuggestions(vm = null) {
            return vm.section.fields;
        },
    },
};
</script>
