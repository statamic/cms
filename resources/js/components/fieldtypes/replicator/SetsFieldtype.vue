<template>

    <div>

        <tabs
            :initial-tabs="tabs"
            :require-tab="config.require_tab"
            :can-define-localizable="false"
            :add-tab-text="__('Add Set Group')"
            :new-tab-text="__('New Set Group')"
            :add-section-text="__('Add Set')"
            :new-section-text="__('New Set')"
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

    data() {
        return {
            tabs: this.value
        }
    },

    methods: {

        tabsUpdated(tabs) {
            this.tabs = tabs;
        }

    }

}
</script>
