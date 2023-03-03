<template>

    <div>

        <div ref="tabs" class="blueprint-tabs flex flex-wrap -mx-2 outline-none">

            <blueprint-tab
                ref="tab"
                v-for="(tab, i) in tabs"
                :key="tab._id"
                :tab="tab"
                :is-single="singleTab"
                :can-define-localizable="canDefineLocalizable"
                :deletable="isTabDeletable(i)"
                @updated="updateTab(i, $event)"
                @deleted="deleteTab(i)"
            />

            <div class="blueprint-add-tab-container w-full md:w-1/2" v-if="!singleTab">
                <button class="blueprint-add-tab-button outline-none" @click="addTab">
                    <div class="text-center flex items-center leading-none">
                        <div class="text-2xl mr-2">+</div>
                        <div v-text="addTabText" />
                    </div>

                    <div class="blueprint-tab-draggable-zone outline-none"></div>
                </button>
            </div>

        </div>

    </div>

</template>

<script>
import uniqid from 'uniqid';
import BlueprintTab from './Tab.vue';
import {Sortable, Plugins} from '@shopify/draggable';
import CanDefineLocalizable from '../fields/CanDefineLocalizable';

let sortableTabs, sortableFields;

export default {

    mixins: [CanDefineLocalizable],

    components: {
        BlueprintTab
    },

    props: {
        initialTabs: {
            type: Array,
            required: true
        },
        addTabText: {
            type: String,
            default: () => __('Add Tab')
        },
        newTabText: {
            type: String,
            default: () => __('New Tab')
        },
        singleTab: {
            type: Boolean,
            default: false
        },
        requireTab: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            tabs: this.initialTabs
        }
    },

    mounted() {
        this.ensureTab();
        this.makeSortable();
    },

    watch: {

        tabs(tabs) {
            this.$emit('updated', tabs);
            this.makeSortable();
        }

    },

    methods: {

        makeSortable() {
            if (! this.singleTab) this.makeTabsSortable();

            this.makeFieldsSortable();
        },

        makeTabsSortable() {
            if (sortableTabs) sortableTabs.destroy();

            sortableTabs = new Sortable(this.$refs.tabs, {
                draggable: '.blueprint-tab',
                handle: '.blueprint-tab-drag-handle',
                mirror: { constrainDimensions: true },
                swapAnimation: { horizontal: true },
                plugins: [Plugins.SwapAnimation]
            }).on('sortable:stop', e => {
                this.tabs.splice(e.newIndex, 0, this.tabs.splice(e.oldIndex, 1)[0]);
            });
        },

        makeFieldsSortable() {
            if (sortableFields) sortableFields.destroy();

            sortableFields = new Sortable(document.querySelectorAll('.blueprint-tab-draggable-zone'), {
                draggable: '.blueprint-tab-field',
                handle: '.blueprint-drag-handle',
                mirror: { constrainDimensions: true, appendTo: 'body' },
                plugins: [Plugins.SwapAnimation]
            }).on('sortable:stop', e => {
                if (e.newContainer.parentElement.classList.contains('blueprint-add-tab-button')) {
                    this.moveFieldToNewTab(e);
                } else {
                    this.moveFieldToExistingTab(e);
                }
            });
        },

        moveFieldToExistingTab(e) {
            const oldTabIndex = Array.prototype.indexOf.call(this.$refs.tabs.children, e.oldContainer.closest('.blueprint-tab'));
            const newTabIndex = Array.prototype.indexOf.call(this.$refs.tabs.children, e.newContainer.closest('.blueprint-tab'));
            const field = this.tabs[oldTabIndex].fields[e.oldIndex];

            if (oldTabIndex === newTabIndex) {
                let fields = this.tabs[newTabIndex].fields
                fields.splice(e.newIndex, 0, fields.splice(e.oldIndex, 1)[0]);
            } else {
                this.tabs[newTabIndex].fields.splice(e.newIndex, 0, field);
                this.tabs[oldTabIndex].fields.splice(e.oldIndex, 1);
            }
        },

        moveFieldToNewTab(e) {
            this.addTab();

            const newTabIndex = this.tabs.length - 1;
            const oldTabIndex = Array.prototype.indexOf.call(this.$refs.tabs.children, e.oldContainer.closest('.blueprint-tab'));
            const field = this.tabs[oldTabIndex].fields[e.oldIndex];

            this.tabs[newTabIndex].fields.splice(e.newIndex, 0, field);
            this.tabs[oldTabIndex].fields.splice(e.oldIndex, 1);

            this.$nextTick(() => this.makeFieldsSortable());
        },

        addTab() {
            this.tabs.push({
                _id: uniqid(),
                display: this.newTabText,
                handle: this.$slugify(this.newTabText, '_'),
                fields: []
            });

            this.$nextTick(() => {
                this.$refs.tab[this.tabs.length-1].focus();
                this.makeSortable();
            })
        },

        deleteTab(i) {
            this.tabs.splice(i, 1);

            this.ensureTab();
        },

        updateTab(i, tab) {
            this.tabs.splice(i, 1, tab);
        },

        ensureTab() {
            if (this.requireTab && this.tabs.length === 0) {
                this.addTab();
            }
        },

        isTabDeletable(i) {
            if (this.tabs.length > 1) return true;

            if (i > 0) return true;

            return !this.requireTab;
        }

    }

}
</script>
