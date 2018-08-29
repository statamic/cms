<template>

    <div class="section-builder" :class="{ 'is-dragging-field': isDraggingField }">

        <div class="tabs-outer">
            <div class="tabs" ref="tabs">
                <a href=""
                    v-for="(i, section) in sections"
                    :class="['tab', { 'active': activeSection === section.id }]"
                    @click.prevent="activeSection = section.id"
                    @mouseenter="hoveredIntoTab(section.id)"
                >
                    {{ section.display }}
                    <span class="icon icon-cross section-delete opacity-25 hover:opacity-75" @click.prevent="deleteSection(i)" v-if="sections.length > 1"></span>
                </a>
            </div>
            <button
                @click.prevent="addSection"
                class="py-1 px-2 opacity-50 hover:opacity-100">
                <span class="icon icon-plus"></span>
            </button>
        </div>

        <div class="sections-container" :class="{ 'has-sidebar': hasSidebar, 'editing-sidebar': editingSidebar }">

                <sections-section
                    v-for="(i, section) in sections"
                    v-show="activeSection === section.id"
                    v-ref=sections
                    :key="i"
                    :section.sync="section"
                    :fieldtypes="fieldtypes"
                    :is-only-section="sections.length === 1"
                    @field-added="addField"
                    @deleted="deleteSection(i)"
                    @fields-sorted="fieldsSorted"
                ></sections-section>

                <div class="other-section-placeholder"
                    v-if="hasSidebar"
                    v-text="editingSidebar ? translate_choice('cp.sections', 2) : sidebarSectionLabel"
                ></div>
            </div>

        </div>
    </div>

</template>


<script>
// import Fieldset from '../../publish/Fieldset';
import { Sortable } from '@shopify/draggable';
import SectionsSection from './Section.vue';

let sortableSections = null;
let sortableFields = null;

export default {

    components: {
        SectionsSection // Naming is hard. Can't just call it section since thats an html tag.
    },

    props: ['fieldtypes', 'sections'],

    data() {
        return {
            activeSection: null,
            isDraggingField: false
        }
    },

    computed: {

        hasSidebar() {
            return this.sidebarSection != null;
        },

        sidebarSection() {
            return _.find(this.sections, { handle: 'sidebar' });
        },

        sidebarSectionLabel() {
            return this.sidebarSection.display || this.sidebarSection.handle;
        },

        sidebarSectionFields() {
            const i = _.findIndex(this.sections, { handle: 'sidebar' });

            if (i == -1) {
                return [];
            }

            return _.map(this.sections[i].fields, field => this.getFieldById(field));
        },

        editingSidebar() {
            if (! this.hasSidebar) return false;

            return this.activeSection === _.find(this.sections, { handle: 'sidebar' }).id;
        }

    },

    mounted() {
        this.activeSection = this.sections[0].id;
        this.$nextTick(() => {
            this.makeSectionsSortable();
            this.makeFieldsSortable();
        });
    },

    watch: {

        activeSection() {
            this.$nextTick(() => this.updateFieldWidths());
        }

    },

    methods: {

        getFieldByName(name) {
            return _.find(this.fieldset.fields, { name });
        },

        getFieldById(id) {
            return _.find(this.fieldset.fields, { id });
        },

        getSectionFields(i) {
            return _.map(this.sections[i].fields, field => this.getFieldById(field));
        },

        makeSectionsSortable() {
            sortableSections = new Sortable(this.$refs.tabs, {
                draggable: '.tab',
                delay: 200,
                mirror: {
                    constrainDimensions: false,
                    cursorOffsetX: 0,
                    yAxis: false
                }
            });

            sortableSections.on('sortable:start', e => {
                this.activeSection = this.sections[e.startIndex].id;
            });

            sortableSections.on('sortable:stop', e => {
                this.sections.splice(e.newIndex, 0, this.sections.splice(e.oldIndex, 1)[0]);
            });
        },

        makeFieldsSortable() {
            sortableFields = new Sortable(this.$el.querySelectorAll('.root-level-section-fields'), {
                draggable: '.root-level-section-field',
                handle: '.root-level-drag-handle',
                appendTo: this.$el,
                mirror: {
                    constrainDimensions: true,
                },
            });

            sortableFields.on('drag:start', e => {
                this.isDraggingField = true;
            });

            sortableFields.on('sortable:stop', (e) => {
                this.isDraggingField = false;

                const oldFieldIndex = e.oldIndex;
                const newFieldIndex = e.newIndex;
                const oldSectionIndex = _.findIndex(this.sections, { id: e.oldContainer.parentElement.__vue__.section.id });
                const newSectionIndex = _.findIndex(this.sections, { id: e.newContainer.parentElement.__vue__.section.id });
                const field = this.sections[oldSectionIndex].fields[oldFieldIndex];

                if (oldSectionIndex === newSectionIndex) {
                    let fields = this.sections[newSectionIndex].fields
                    fields.splice(newFieldIndex, 0, fields.splice(oldFieldIndex, 1)[0]);
                } else {
                    this.sections[newSectionIndex].fields.splice(newFieldIndex, 0, field);
                    this.sections[oldSectionIndex].fields.splice(oldFieldIndex, 1);
                    // Force a re-render
                    const scrollpos = window.scrollY;
                    this.sections = JSON.parse(JSON.stringify(this.sections));
                    sortableFields.destroy();
                    this.$nextTick(() => {
                        this.makeFieldsSortable();
                        window.scroll(0, scrollpos);
                    });
                }
            });

            this.$nextTick(() => this.updateFieldWidths());
        },

        addSection() {
            const count = this.sections.length + 1;
            const section = {
                display: 'Section ' + count,
                handle: 'section_' + count,
                id: 'section_' + count,
                fields: []
            };

            this.sections.push(section);
            this.activeSection = section.id;

            this.$nextTick(() => {
                const i = this.sections.length - 1;
                sortableSections.destroy();
                this.makeSectionsSortable();
                sortableFields.destroy();
                this.makeFieldsSortable();

                this.$refs.sections[i].focus();
            });
        },

        deleteSection(i) {
            // Prevent deleting the last section
            if (i === 0 && this.sections.length === 1) return;

            // Put orphaned fields into the first section.
            const newIndex = i === 0 ? 1 : 0; // If we're deleting the first section, put it in the second.
            this.sections[newIndex].fields.splice(this.sections[newIndex].fields.length, 0, ...this.sections[i].fields);

            // Delete it.
            this.sections.splice(i, 1);

            this.activeSection = this.sections[newIndex].id;
        },

        getSectionComponentByHandle(handle) {
            if (handle === 'sidebar') {
                return this.$refs.sidebarSection;
            }

            const index =  _.findIndex(this.sections, { handle });
            return this.$refs.sections[index];
        },

        createNewField(fieldtype) {
            const fieldsLength = this.fieldset.fields.length || 0;
            const count = fieldsLength + 1;

            const tmp = _.findWhere(this.fieldtypes, { name: fieldtype });
            let field = $.extend({}, tmp);

            field.type = field.name;
            field.name = 'field_' + count;
            field.id = 'field_' + count;
            field.display = 'Field ' + count;
            field.instructions = null;
            field.localizable = false;
            field.width = 100;
            field.isNew = true;
            delete field.config;
            delete field.label;
            delete field.canBeValidated;
            delete field.canBeLocalized;
            delete field.canHaveDefault;

            return field;
        },

        hoveredIntoTab(section) {
            if (this.isDraggingField) {
                this.activeSection = section;
            }
        },

        updateFieldWidths() {
            _.each(this.$refs.sections, component => component.updateFieldWidths());
        }

    }

}
</script>
