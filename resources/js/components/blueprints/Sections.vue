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
                :is-single="singleSection"
                :can-define-localizable="canDefineLocalizable"
                :deletable="isSectionDeletable(i)"
                :tab-id="tabId"
                @updated="updateSection(i, $event)"
                @deleted="deleteSection(i)"
            />

            <div class="blueprint-add-section-container w-full md:w-1/2" v-if="!singleSection">
                <button class="blueprint-add-section-button outline-none" @click="addSection">
                    <div class="text-center flex items-center leading-none">
                        <div class="text-2xl mr-1">+</div>
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
                handle: this.$slugify(this.newSectionText, '_'),
                fields: []
            };

            this.sections.push(section);

            return section;
        },

        deleteSection(i) {
            this.sections.splice(i, 1);

            this.ensureSection();
        },

        updateSection(i, section) {
            this.sections.splice(i, 1, section);
        },

        isSectionDeletable(i) {
            if (this.sections.length > 1) return true;

            if (i > 0) return true;

            return !this.requireSection;
        }

    }

}
</script>
