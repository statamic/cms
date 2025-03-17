<template>
    <div>
        <div v-if="!singleTab && tabs.length > 0" class="tabs-container relative">
            <div
                ref="tabs"
                class="tabs flex flex-1 space-x-3 overflow-auto ltr:pr-6 rtl:space-x-reverse rtl:pl-6"
                role="tablist"
            >
                <tab
                    ref="tab"
                    v-for="tab in tabs"
                    :key="tab._id"
                    :tab="tab"
                    :current-tab="currentTab"
                    :show-instructions="showTabInstructionsField"
                    :edit-text="editTabText"
                    @selected="selectTab(tab._id)"
                    @removed="removeTab(tab._id)"
                    @updated="updateTab(tab._id, $event)"
                    @mouseenter="mouseEnteredTab(tab._id)"
                />
                <div class="fade-left" v-if="canScrollLeft" />
            </div>
            <div class="fade-right ltr:right-10 rtl:left-10" />
            <button
                class="btn-round relative top-1 flex items-center justify-center ltr:ml-2 rtl:mr-2"
                @click="addAndEditTab"
                v-tooltip="addTabText"
            >
                <svg-icon name="add" class="h-3 w-3" />
            </button>
        </div>
        <button v-if="!singleTab && tabs.length === 0" class="btn" @click="addAndEditTab" v-text="addTabText" />
        <div v-if="errors" class="-mt-2">
            <small class="help-block text-red-500" v-for="(error, i) in errors" :key="i" v-text="error" />
        </div>
        <tab-content
            v-for="tab in tabs"
            ref="tabContent"
            :key="tab._id"
            :tab="tab"
            v-show="currentTab === tab._id"
            :show-section-handle-field="showSectionHandleField"
            :show-section-hide-field="showSectionHideField"
            :new-section-text="newSectionText"
            :edit-section-text="editSectionText"
            :add-section-text="addSectionText"
            :can-define-localizable="canDefineLocalizable"
            @updated="updateTab(tab._id, $event)"
        />
    </div>
</template>

<script>
import { Sortable, Plugins } from '@shopify/draggable';
import uniqid from 'uniqid';
import Tab from './Tab.vue';
import TabContent from './TabContent.vue';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';

export default {
    mixins: [CanDefineLocalizable],

    components: {
        Tab,
        TabContent,
    },

    props: {
        initialTabs: {
            type: Array,
            required: true,
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
        addTabText: {
            type: String,
            default: () => __('Add Tab'),
        },
        editTabText: {
            type: String,
            default: () => __('Edit Tab'),
        },
        newTabText: {
            type: String,
            default: () => __('New Tab'),
        },
        singleTab: {
            type: Boolean,
            default: false,
        },
        requireSection: {
            type: Boolean,
            default: true,
        },
        showTabInstructionsField: {
            type: Boolean,
            default: false,
        },
        showSectionHandleField: {
            type: Boolean,
            default: false,
        },
        showSectionHideField: {
            type: Boolean,
            default: false,
        },
        errors: {
            type: Array,
        },
    },

    data() {
        return {
            tabs: this.initialTabs,
            currentTab: this.initialTabs.length ? this.initialTabs[0]._id : null,
            lastInteractedTab: null,
            hiddenTabs: [],
            tabsAreScrolled: false,
            canScrollLeft: false,
            canScrollRight: false,
            sortableTabs: null,
            sortableSections: null,
            sortableFields: null,
        };
    },

    watch: {
        tabs(tabs) {
            this.$emit('updated', tabs);
            this.makeSortable();
        },
    },

    mounted() {
        this.ensureTab();
        this.makeSortable();
    },

    unmounted() {
        if (this.sortableTabs) this.sortableTabs.destroy();
        if (this.sortableSections) this.sortableSections.destroy();
        if (this.sortableFields) this.sortableFields.destroy();
    },

    methods: {
        ensureTab() {
            if (this.requireSection && this.tabs.length === 0) {
                this.addTab();
            }
        },

        makeSortable() {
            if (!this.singleTab) this.makeTabsSortable();

            this.makeSectionsSortable();

            this.makeFieldsSortable();
        },

        makeTabsSortable() {
            if (this.sortableTabs) this.sortableTabs.destroy();

            this.sortableTabs = new Sortable(this.$refs.tabs, {
                draggable: '.blueprint-tab',
                mirror: { constrainDimensions: true },
                swapAnimation: { horizontal: true },
                plugins: [Plugins.SwapAnimation],
                distance: 10,
            })
                .on('sortable:stop', (e) => {
                    this.tabs.splice(e.newIndex, 0, this.tabs.splice(e.oldIndex, 1)[0]);
                })
                .on('mirror:create', (e) => e.cancel());
        },

        makeSectionsSortable() {
            if (this.sortableSections) this.sortableSections.destroy();

            this.sortableSections = new Sortable(this.$el.querySelectorAll('.blueprint-sections'), {
                draggable: '.blueprint-section',
                handle: '.blueprint-section-drag-handle',
                mirror: { constrainDimensions: true, appendTo: 'body' },
            })
                .on('drag:start', (e) => (this.lastInteractedTab = this.currentTab))
                .on('drag:stop', (e) => (this.lastInteractedTab = null))
                .on('sortable:sort', (e) => (this.lastInteractedTab = this.currentTab))
                .on('sortable:stop', (e) => this.sectionHasBeenDropped(e));
        },

        makeFieldsSortable() {
            if (this.sortableFields) this.sortableFields.destroy();

            this.sortableFields = new Sortable(this.$el.querySelectorAll('.blueprint-section-draggable-zone'), {
                draggable: '.blueprint-section-field',
                handle: '.blueprint-drag-handle',
                mirror: { constrainDimensions: true, appendTo: 'body' },
            })
                .on('drag:start', (e) => (this.lastInteractedTab = this.currentTab))
                .on('drag:stop', (e) => (this.lastInteractedTab = null))
                .on('sortable:stop', (e) => this.fieldHasBeenDropped(e));
        },

        sectionHasBeenDropped(e) {
            const oldTabId = e.oldContainer.dataset.tab;
            const oldIndex = e.oldIndex;
            let newTabId = e.newContainer.dataset.tab;
            let newIndex = e.newIndex;

            if (this.lastInteractedTab !== this.currentTab && this.currentTab !== newTabId) {
                // Dragged over tab but haven't dragged into a droppable spot yet.
                // In this case we'll assume they want to drop it at the top of the tab.
                newTabId = this.currentTab;
                newIndex = 0;
            }

            const hasMovedTabs = oldTabId !== newTabId;

            if (hasMovedTabs) {
                // Rearrange sections within the tabs.
                const oldTab = this.tabs.find((tab) => tab._id === oldTabId);
                const newTab = this.tabs.find((tab) => tab._id === newTabId);
                const section = oldTab.sections.splice(oldIndex, 1)[0];
                newTab.sections.splice(newIndex, 0, section);
                this.updateTab(oldTabId, oldTab);
                this.updateTab(newTabId, newTab);
            } else {
                // Update the section within the tab.
                const tab = this.tabs.find((tab) => tab._id === oldTabId);
                tab.sections.splice(newIndex, 0, tab.sections.splice(oldIndex, 1)[0]);
                this.updateTab(oldTabId, tab);
            }
        },

        fieldHasBeenDropped(e) {
            const oldTabId = e.oldContainer.dataset.tab;
            let newTabId = e.newContainer.dataset.tab;
            let newTab = this.tabs.find((tab) => tab._id === newTabId);
            let newIndex = e.newIndex;
            let newSection;

            if (e.newContainer.parentElement.classList.contains('blueprint-add-section-button')) {
                newSection = this.$refs.tabContent.find((vm) => vm.tab._id === newTabId).addSection();
            } else {
                newSection = newTab.sections.find((section) => section._id === e.newContainer.dataset.section);
            }

            if (this.lastInteractedTab !== this.currentTab && this.currentTab !== newTabId) {
                // Dragged over tab but haven't dragged into a droppable spot yet.
                // In this case we'll assume they want to dropped into the first section of that tab.
                newTabId = this.currentTab;
                newTab = this.tabs.find((tab) => tab._id === newTabId);
                newSection = newTab.sections[0];
                newIndex = 0;
            }

            const oldTab = this.tabs.find((tab) => tab._id === oldTabId);
            const oldSection = oldTab.sections.find((section) => section._id === e.oldContainer.dataset.section);

            const field = oldSection.fields.splice(e.oldIndex, 1)[0];
            newSection.fields.splice(newIndex, 0, field);

            this.updateTab(oldTabId, oldTab);
            this.updateTab(newTabId, newTab);

            this.$nextTick(() => this.makeFieldsSortable());
        },

        updateTab(tabId, tab) {
            const index = this.tabs.findIndex((tab) => tab._id === tabId);
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
                handle: snake_case(this.newTabText),
                instructions: null,
                icon: null,
                sections: [],
            });

            this.selectTab(id);

            this.$nextTick(() => this.$refs.tabContent.find((vm) => vm.tab._id === id).addSection());
        },

        addAndEditTab() {
            this.addTab();
            this.$nextTick(() => this.$refs.tab.find((vm) => vm.tab._id === this.currentTab).edit());
        },

        removeTab(tabId) {
            this.tabs = this.tabs.filter((tab) => tab._id !== tabId);

            this.selectTab(this.tabs.length ? this.tabs[0]._id : null);

            this.ensureTab();
        },
    },
};
</script>
