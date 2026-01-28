<template>
    <TabContent :name="tab._id">
        <sections
            ref="sections"
            :tab-id="tab._id"
            :initial-sections="tab.sections"
            :new-section-text="newSectionText"
            :add-section-text="addSectionText"
            :edit-section-text="editSectionText"
            :show-section-handle-field="showSectionHandleField"
            :show-section-hide-field="showSectionHideField"
            :can-define-localizable="canDefineLocalizable"
            @updated="sectionsUpdated($event)"
        />
    </TabContent>
</template>

<script>
import Sections from './Sections.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';
import { TabContent } from '@/components/ui';

export default {
    mixins: [CanDefineLocalizable],

    components: {
        Sections,
        TabContent,
    },

    props: {
        tab: {
            type: Object,
        },
        showSectionHandleField: {
            type: Boolean,
            default: false,
        },
        showSectionHideField: {
            type: Boolean,
            default: false,
        },
        addSectionText: {
            type: String,
        },
        editSectionText: {
            type: String,
        },
        newSectionText: {
            type: String,
        },
    },

    methods: {
        addSection() {
            return this.$refs.sections.addSection();
        },

        sectionsUpdated(sections) {
            let tab = { ...this.tab, ...{ sections } };
            this.$emit('updated', tab);
        },
    },
};
</script>
