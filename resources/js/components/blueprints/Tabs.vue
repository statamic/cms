<template>

    <div>

        <div ref="tabs" class="mb-5 flex">
            <div
                v-for="tab in tabs"
                :key="tab._id"
                class="blueprint-tab card px-5 py-2 mr-2 cursor-pointer flex"
                :class="{ 'bg-blue-100': currentTab === tab._id }"
                @click="selectTab(tab._id)"
                @mouseenter="mouseEnteredTab(tab._id)"
            >
                {{ tab.display }}
                <div @click="removeTab(tab._id)">
                    <svg-icon name="delete-x" class="w-2 h-2 ml-2 text-gray-600 hover:text-gray-800" />
                </div>
            </div>
            <div class="card px-5 py-2 cursor-pointer" @click="addTab" v-text="addTabText" />
        </div>

        <div
            v-for="(tab, i) in tabs"
            :key="tab._id"
            v-show="currentTab === tab._id"
        >
            <input type="text" v-model="tab.display" class="input-text" />
            <input type="text" v-model="tab.handle" class="input-text font-mono text-sm" />

            <sections
                ref="sections"
                :tab-id="tab._id"
                :initial-sections="tab.sections"
                :new-section-text="newSectionText"
                :add-section-text="addSectionText"
                @updated="sectionsUpdated(tab._id, $event)"
            />

        </div>

    </div>

</template>

<script>
import {Sortable, Plugins} from '@shopify/draggable';
import Sections from './Sections.vue';
import uniqid from 'uniqid';

let sortableTabs, sortableSections, sortableFields;

export default {

    components: {
        Sections
    },

    props: {
        initialTabs: {
            type: Array,
            required: true
        },
        addSectionText: {
            type: String,
        },
        newSectionText: {
            type: String,
        },
        addTabText: {
            type: String,
            default: () => __('Add Tab')
        },
        newTabText: {
            type: String,
            default: () => __('New Tab')
        },
    },

    data() {
        return {
            tabs: this.initialTabs,
            currentTab: this.initialTabs.length ? this.initialTabs[0]._id : null,
            lastInteractedTab: null
        }
    },

    watch: {

        tabs(tabs) {
            this.$emit('updated', tabs);
        }

    },

    mounted() {
        // this.ensureTab();
        this.makeSortable();
    },

    methods: {

        ensureTab() {
            if (this.requireTab && this.tabs.length === 0) {
                this.addTab();
            }
        },

        makeSortable() {
            if (! this.singleTab) this.makeTabsSortable();

            // if (! this.singleSection) { // can/should sections be singular now?
                this.makeSectionsSortable();
            // }

            this.makeFieldsSortable();
        },

        makeTabsSortable() {
            if (sortableTabs) sortableTabs.destroy();

            sortableTabs = new Sortable(this.$refs.tabs, {
                draggable: '.blueprint-tab',
                mirror: { constrainDimensions: true },
                swapAnimation: { horizontal: true },
                plugins: [Plugins.SwapAnimation],
                distance: 10,
            }).on('sortable:stop', e => {
                this.tabs.splice(e.newIndex, 0, this.tabs.splice(e.oldIndex, 1)[0]);
            }).on('mirror:create', (e) => e.cancel());
        },

        makeSectionsSortable() {
            if (sortableSections) sortableSections.destroy();

            sortableSections = new Sortable(document.querySelectorAll('.blueprint-sections'), {
                draggable: '.blueprint-section',
                handle: '.blueprint-section-drag-handle',
                mirror: { constrainDimensions: true, appendTo: 'body' },
                plugins: [Plugins.SwapAnimation]
            })
            .on('drag:start', e => this.lastInteractedTab = this.currentTab)
            .on('drag:stop', e => this.lastInteractedTab = null)
            .on('sortable:sort', e => this.lastInteractedTab = this.currentTab)
            .on('sortable:stop', e => this.sectionHasBeenDropped(e));
        },


        makeFieldsSortable() {
            if (sortableFields) sortableFields.destroy();

            sortableFields = new Sortable(this.$el.querySelectorAll('.blueprint-section-draggable-zone'), {
                draggable: '.blueprint-section-field',
                handle: '.blueprint-drag-handle',
                mirror: { constrainDimensions: true, appendTo: 'body' },
                plugins: [Plugins.SwapAnimation]
            })
            .on('drag:start', e => this.lastInteractedTab = this.currentTab)
            .on('drag:stop', e => this.lastInteractedTab = null)
            .on('sortable:stop', e => this.fieldHasBeenDropped(e));
        },

        sectionsUpdated(tabId, sections) {
            const tab = this.tabs.find(tab => tab._id === tabId);
            this.updateTab(tabId, {...tab, sections});
        },

        sectionHasBeenDropped(e) {
            const oldTabId = e.oldContainer.dataset.tab;
            const oldIndex = e.oldIndex;
            let newTabId = e.newContainer.dataset.tab;
            let newIndex = e.newIndex;

            if (this.lastInteractedTab !== this.currentTab
            && this.currentTab !== newTabId) {
                // Dragged over tab but haven't dragged into a droppable spot yet.
                // In this case we'll assume they want to drop it at the top of the tab.
                newTabId = this.currentTab;
                newIndex = 0;
            }

            const hasMovedTabs = oldTabId !== newTabId;

            if (hasMovedTabs) {
                // Rearrange sections within the tabs.
                const oldTab = this.tabs.find(tab => tab._id === oldTabId);
                const newTab = this.tabs.find(tab => tab._id === newTabId);
                const section = oldTab.sections.splice(oldIndex, 1)[0];
                newTab.sections.splice(newIndex, 0, section);
                this.updateTab(oldTabId, oldTab);
                this.updateTab(newTabId, newTab);
            } else {
                // Update the section within the tab.
                const tab = this.tabs.find(tab => tab._id === oldTabId);
                tab.sections.splice(newIndex, 0, tab.sections.splice(oldIndex, 1)[0]);
                this.updateTab(oldTabId, tab);
            }
        },

        fieldHasBeenDropped(e) {
            const oldTabId = e.oldContainer.dataset.tab;
            let newTabId = e.newContainer.dataset.tab;
            let newTab = this.tabs.find(tab => tab._id === newTabId);
            let newIndex = e.newIndex
            let newSection;

            if (e.newContainer.parentElement.classList.contains('blueprint-add-section-button')) {
                newSection = this.$refs.sections.find(vm => vm.tabId === newTabId).addSection();
            } else {
                newSection = newTab.sections.find(section => section._id === e.newContainer.dataset.section);
            }

            if (this.lastInteractedTab !== this.currentTab
            && this.currentTab !== newTabId) {
                // Dragged over tab but haven't dragged into a droppable spot yet.
                // In this case we'll assume they want to dropped into the first section of that tab.
                newTabId = this.currentTab;
                newTab = this.tabs.find(tab => tab._id === newTabId);
                newSection = newTab.sections[0];
                newIndex = 0;
            }

            const oldTab = this.tabs.find(tab => tab._id === oldTabId);
            const oldSection = oldTab.sections.find(section => section._id === e.oldContainer.dataset.section);

            const field = oldSection.fields.splice(e.oldIndex, 1)[0];
            newSection.fields.splice(newIndex, 0, field);

            this.updateTab(oldTabId, oldTab);
            this.updateTab(newTabId, newTab);

            this.$nextTick(() => this.makeFieldsSortable());
        },

        updateTab(tabId, tab) {
            const index = this.tabs.findIndex(tab => tab._id === tabId);
            this.tabs.splice(index, 1, tab);
        },

        selectTab(tabId) {
            this.currentTab = tabId;
        },

        mouseEnteredTab(tabId) {
            if (this.lastInteractedTab) this.selectTab(tabId);
        },

        addTab() {
            const id = uniqid();

            this.tabs.push({
                _id: id,
                display: this.newTabText,
                handle: this.$slugify(this.newTabText, '_'),
                sections: []
            });

            this.selectTab(id);
        },

        removeTab(tabId) {
            this.tabs = this.tabs.filter(tab => tab._id !== tabId);

            this.selectTab(this.tabs.length ? this.tabs[0]._id : null);
        },

    }

}
</script>
