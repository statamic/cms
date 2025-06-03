<template>
    <div>
        <div ref="sections" class="blueprint-sections flex flex-wrap outline-hidden" :data-tab="tabId">
            <blueprint-section
                ref="section"
                v-for="(section, i) in sections"
                :key="section._id"
                :section="section"
                :can-define-localizable="canDefineLocalizable"
                :tab-id="tabId"
                :show-handle-field="showSectionHandleField"
                :show-hide-field="showSectionHideField"
                :edit-text="editSectionText"
                @updated="updateSection(i, $event)"
                @deleted="deleteSection(i)"
            />

            <button class="w-full flex gap-2 items-center justify-center relative min-h-24 text-gray-500 hover:text-gray-700 dark:hover:text-gray-400 cursor-pointer border border-dashed border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600 rounded-xl outline-hidden" @click="addAndEditSection">
                <ui-icon name="plus" class="size-4" />
                <div v-text="addSectionText" />
            </button>
        </div>
    </div>
</template>

<script>
import uniqid from 'uniqid';
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
        showSectionHideField: {
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
