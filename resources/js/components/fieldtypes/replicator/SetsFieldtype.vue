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
import SuggestsConditionalFields from '../../blueprints/SuggestsConditionalFields';
import Tabs from '../../blueprints/Tabs.vue';

export default {

    mixins: [Fieldtype, SuggestsConditionalFields],

    components: {
        Tabs
    },

    data() {
        return {
            tabs: this.value
        }
    },

    provide: {
        isInsideSet: true,
    },

    methods: {

        tabsUpdated(tabs) {
            this.update(tabs);
        },

        getSectionFieldsForConditionSuggestions(vm = null) {
            return vm.section.fields;
        }

    }

}
</script>
