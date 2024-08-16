<template>

    <div>

        <div
            ref="sections"
            class="blueprint-sections flex flex-wrap -mx-2 outline-none"
            :data-tab="tabId"
        >

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

            <div class="blueprint-add-section-container w-full">
                <button class="blueprint-add-section-button outline-none" @click="addAndEditSection">
                    <div class="text-center flex items-center leading-none">
                        <svg-icon name="micro/plus" class="h-3 w-3 rtl:ml-2 ltr:mr-2" />
                        <div v-text="addSectionText" />
                    </div>

                    <div
                        class="blueprint-section-draggable-zone outline-none"
                        :data-tab="tabId"
                    />
                </button>
            </div>

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
        BlueprintSection
    },

    props: {
        tabId: {
            type: String,
        },
        initialSections: {
            type: Array,
            required: true
        },
        addSectionText: {
            type: String,
            default: () => __('Add Section')
        },
        editSectionText: {
            type: String,
            default: () => __('Edit Section')
        },
        newSectionText: {
            type: String,
            default: () => __('New Section')
        },
        singleSection: {
            type: Boolean,
            default: false
        },
        requireSection: {
            type: Boolean,
            default: true
        },
        showSectionHandleField: {
            type: Boolean,
            default: false
        },
        showSectionHideField: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            sections: this.initialSections
        }
    },

    watch: {

        sections(sections) {
            this.$emit('updated', sections);
        }

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
                fields: []
            };

            this.sections.push(section);

            return section;
        },

        addAndEditSection() {
            const section = this.addSection();

            this.$nextTick(() => {
                this.$refs.section.find(vm => vm.section._id === section._id).edit();
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
        }

    }

}
</script>
