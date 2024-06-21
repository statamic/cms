<template>
    <div
        role="tabpanel"
        :aria-labelledby="`tab-${tab._id}`"
        :id="`tab-panel-${tab._id}`">
        <sections
            ref="sections"
            :tab-id="tab._id"
            :initial-sections="tab.sections"
            :new-section-text="newSectionText"
            :add-section-text="addSectionText"
            :edit-section-text="editSectionText"
            :show-section-handle-field="showSectionHandleField"
            :show-section-hide-field="showSectionHideField"
            @updated="sectionsUpdated($event)"
        />
    </div>
</template>

<script>
import Sections from './Sections.vue';

export default {

    components: {
        Sections,
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
            let tab = {...this.tab, ...{ sections }};
            this.$emit('updated', tab);
        },

    }

}
</script>
