<template>

    <div>

        <div ref="sections" class="blueprint-sections flex flex-wrap -mx-1 outline-none">

            <blueprint-section
                ref="section"
                v-for="(section, i) in sections"
                :key="section._id"
                :section="section"
                :is-single="singleSection"
                :can-define-localizable="canDefineLocalizable"
                :deletable="isSectionDeletable(i)"
                @updated="updateSection(i, $event)"
                @deleted="deleteSection(i)"
            />

            <div class="blueprint-add-section-container w-full md:w-1/2" v-if="!singleSection">
                <button class="blueprint-add-section-button outline-none" @click="addSection">
                    <div class="text-center flex items-center leading-none">
                        <div class="text-2xl mr-1">+</div>
                        <div v-text="addSectionText" />
                    </div>

                    <div class="blueprint-section-draggable-zone outline-none"></div>
                </button>
            </div>

        </div>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import BlueprintSection from './Section.vue';
import {Sortable, Plugins} from '@shopify/draggable';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';

let sortableSections, sortableFields;

export default {

    mixins: [CanDefineLocalizable],

    components: {
        BlueprintSection
    },

    props: {
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

    mounted() {
        this.ensureSection();
        this.makeSortable();
    },

    watch: {

        sections(sections) {
            this.$emit('updated', sections);
            this.makeSortable();
        }

    },

    methods: {

        makeSortable() {
            if (! this.singleSection) this.makeSectionsSortable();

            this.makeFieldsSortable();
        },

        makeSectionsSortable() {
            if (sortableSections) sortableSections.destroy();

            sortableSections = new Sortable(this.$refs.sections, {
                draggable: '.blueprint-section',
                handle: '.blueprint-section-drag-handle',
                mirror: { constrainDimensions: true },
                swapAnimation: { horizontal: true },
                plugins: [Plugins.SwapAnimation]
            }).on('sortable:stop', e => {
                this.sections.splice(e.newIndex, 0, this.sections.splice(e.oldIndex, 1)[0]);
            });
        },

        makeFieldsSortable() {
            if (sortableFields) sortableFields.destroy();

            sortableFields = new Sortable(document.querySelectorAll('.blueprint-section-draggable-zone'), {
                draggable: '.blueprint-section-field',
                handle: '.blueprint-drag-handle',
                mirror: { constrainDimensions: true },
                plugins: [Plugins.SwapAnimation]
            }).on('sortable:stop', e => {
                if (e.newContainer.parentElement.classList.contains('blueprint-add-section-button')) {
                    this.moveFieldToNewSection(e);
                } else {
                    this.moveFieldToExistingSection(e);
                }
            });
        },

        moveFieldToExistingSection(e) {
            const oldSectionIndex = Array.prototype.indexOf.call(this.$refs.sections.children, e.oldContainer.closest('.blueprint-section'));
            const newSectionIndex = Array.prototype.indexOf.call(this.$refs.sections.children, e.newContainer.closest('.blueprint-section'));
            const field = this.sections[oldSectionIndex].fields[e.oldIndex];

            if (oldSectionIndex === newSectionIndex) {
                let fields = this.sections[newSectionIndex].fields
                fields.splice(e.newIndex, 0, fields.splice(e.oldIndex, 1)[0]);
            } else {
                this.sections[newSectionIndex].fields.splice(e.newIndex, 0, field);
                this.sections[oldSectionIndex].fields.splice(e.oldIndex, 1);
            }
        },

        moveFieldToNewSection(e) {
            this.addSection();

            const newSectionIndex = this.sections.length - 1;
            const oldSectionIndex = Array.prototype.indexOf.call(this.$refs.sections.children, e.oldContainer.closest('.blueprint-section'));
            const field = this.sections[oldSectionIndex].fields[e.oldIndex];

            this.sections[newSectionIndex].fields.splice(e.newIndex, 0, field);
            this.sections[oldSectionIndex].fields.splice(e.oldIndex, 1);

            this.$nextTick(() => this.makeFieldsSortable());
        },

        addSection() {
            this.sections.push({
                _id: uniqid(),
                display: this.newSectionText,
                handle: this.$slugify(this.newSectionText, '_'),
                fields: []
            });

            this.$nextTick(() => {
                this.$refs.section[this.sections.length-1].focus();
                this.makeSortable();
            })
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

        isSectionDeletable(i) {
            if (this.sections.length > 1) return true;

            if (i > 0) return true;

            return !this.requireSection;
        }

    }

}
</script>
