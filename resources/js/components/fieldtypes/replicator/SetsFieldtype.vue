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
            :show-section-card-field="showCardLayoutField"
            show-section-hide-field
            @updated="tabsUpdated"
        />
    </div>
</template>

<script>
import Fieldtype from '../Fieldtype.vue';
import SuggestsConditionalFields from '../../blueprints/SuggestsConditionalFields';
import Tabs from '../../blueprints/Tabs.vue';

function stripCardLayout(tabs) {
    return tabs.map((tab) => ({
        ...tab,
        sections: tab.sections.map((section) => {
            const { card, ...rest } = section;

            return rest;
        }),
    }));
}

export default {
    mixins: [Fieldtype, SuggestsConditionalFields],

    components: {
        Tabs,
    },

    computed: {
        showCardLayoutField() {
            return this.config.show_card_layout_field === true;
        },
    },

    data() {
        return {
            tabs: this.config.show_card_layout_field === true ? this.value : stripCardLayout(this.value),
        };
    },

    provide: {
        isInsideSet: true,
    },

    methods: {
        tabsUpdated(tabs) {
            this.update(this.showCardLayoutField ? tabs : stripCardLayout(tabs));
        },

        getSectionFieldsForConditionSuggestions(vm = null) {
            return vm.section.fields;
        },
    },
};
</script>
