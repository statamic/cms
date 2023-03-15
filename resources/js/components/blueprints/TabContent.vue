<template>
    <div
        role="tabpanel"
        :aria-labelledby="`tab-${tab._id}`"
        :id="`tab-panel-${tab._id}`">
        <input type="text"
            :value="tab.display"
            @input="fieldUpdated('display', $event.target.value)"
            class="input-text" />
        <input type="text"
            :value="tab.handle"
            @input="fieldUpdated('handle', $event.target.value)"
            class="input-text font-mono text-sm" />
        <input type="text"
            :value="tab.instructions"
            @input="fieldUpdated('instructions', $event.target.value)"
            class="input-text text-sm"
            v-if="showInstructions" />
        <sections
            ref="sections"
            :tab-id="tab._id"
            :initial-sections="tab.sections"
            :new-section-text="newSectionText"
            :add-section-text="addSectionText"
            :show-section-handle-field="showSectionHandleField"
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
        showInstructions: {
            type: Boolean,
            default: false,
        },
        showSectionHandleField: {
            type: Boolean,
            default: false,
        },
        addSectionText: {
            type: String,
        },
        newSectionText: {
            type: String,
        },
    },

    data() {
        return {
            handleSyncedWithDisplay: false,
        }
    },

    created() {
        // This logic isn't ideal, but it was better than passing along a 'isNew' boolean and having
        // to deal with stripping it out and making it not new, etc. Good enough for a quick win.
        if (!this.tab.handle || this.tab.handle == 'new_tab' || this.tab.handle == 'new_set_group') {
            this.handleSyncedWithDisplay = true;
        }
    },

    methods: {

        addSection() {
            this.$refs.sections.addSection();
        },

        sectionsUpdated(sections) {
            let tab = {...this.tab, ...{ sections }};
            this.$emit('updated', tab);
        },

        fieldUpdated(handle, value) {
            let tab = {...this.tab, ...{ [handle]: value }};

            if (handle === 'display' && this.handleSyncedWithDisplay) {
                tab.handle = this.$slugify(value, '_');
            }

            if (handle === 'handle') {
                this.handleSyncedWithDisplay = false;
            }

            this.$emit('updated', tab);
        }

    }

}
</script>
