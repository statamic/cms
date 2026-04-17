<template>
    <div>
        <div ref="sections" class="blueprint-sections flex flex-wrap outline-hidden" :data-tab="tabId" tabindex="-1">
            <BlueprintSection
                ref="section"
                v-for="(section, i) in sections"
                :key="section._id"
                :section="section"
                :can-define-localizable="canDefineLocalizable"
                :tab-id="tabId"
                :show-handle-field="showSectionHandleField"
                :show-collapsible-field="showSectionCollapsibleField"
                :show-hide-field="showSectionHideField"
                :exclude-fieldset="excludeFieldset"
                :with-command-palette="withCommandPalette"
                :edit-text="editSectionText"
                @updated="updateSection(i, $event)"
                @deleted="deleteSection(i)"
            />

            <div class="blueprint-add-section-container w-full">
                <button class="blueprint-add-section-button" @click="addAndEditSection">
                    <div class="flex items-center gap-2">
                        <ui-icon name="plus" class="size-4" />
                        <div v-text="addSectionText" />
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { nanoid as uniqid } from 'nanoid';
import BlueprintSection from './Section.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';

export default {
    mixins: [CanDefineLocalizable],

    components: {
        BlueprintSection,
    },

    props: {
        tabId: {
            type: String,
        },
        initialSections: {
            type: Array,
            required: true,
        },
        addSectionText: {
            type: String,
            default: () => __('Add Section'),
        },
        editSectionText: {
            type: String,
            default: () => __('Edit Section'),
        },
        newSectionText: {
            type: String,
            default: () => __('New Section'),
        },
        singleSection: {
            type: Boolean,
            default: false,
        },
        requireSection: {
            type: Boolean,
            default: true,
        },
        showSectionHandleField: {
            type: Boolean,
            default: false,
        },
	    showSectionCollapsibleField: {
			type: Boolean,
		    default: false,
	    },
        showSectionHideField: {
            type: Boolean,
            default: false,
        },
        excludeFieldset: {
            type: String,
            default: null,
        },
        withCommandPalette: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            sections: this.initialSections,
        };
    },

    watch: {
        sections(sections) {
            this.$emit('updated', sections);
        },
    },

    methods: {
        addSection() {
            const section = {
                _id: uniqid(),
                display: this.newSectionText,
                instructions: null,
                collapsible: false,
                collapsed: false,
                icon: null,
                hide: null,
                handle: snake_case(this.newSectionText),
                fields: [],
            };

            this.sections.push(section);

            return section;
        },

        addAndEditSection() {
            const section = this.addSection();

            this.$nextTick(() => {
                this.$refs.section.find((vm) => vm.section._id === section._id).edit();
            });
        },

        deleteSection(i) {
            this.sections.splice(i, 1);

            this.ensureSection();
        },

        updateSection(i, section) {
            this.sections.splice(i, 1, section);
        },

        ensureSection() {
            if (this.requireSection && this.sections.length === 0) {
                this.addSection();
            }
        },
    },
};
</script>
