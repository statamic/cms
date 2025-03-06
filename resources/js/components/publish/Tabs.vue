<template>
    <element-container @resized="containerWasResized">
        <div>
            <!-- Tabs -->
            <div v-if="showTabs" class="tabs-container flex items-center">
                <div
                    class="publish-tabs tabs"
                    :class="{ 'tabs-scrolled': canScrollLeft }"
                    ref="tabs"
                    role="tablist"
                    :aria-label="__('Edit Content')"
                    @keydown.prevent.arrow-left="activatePreviousTab"
                    @keydown.prevent.arrow-right="activateNextTab"
                    @keydown.prevent.arrow-up="activatePreviousTab"
                    @keydown.prevent.arrow-down="activateNextTab"
                    @keydown.prevent.home="activateFirstTab"
                    @keydown.prevent.end="activateLastTab"
                    @mousewheel.prevent="scrollTabs"
                    @scroll="updateScrollHints"
                >
                    <button
                        v-for="tab in mainTabs"
                        class="tab-button"
                        ref="tab"
                        :key="tab.handle"
                        :class="{
                            active: isActive(tab.handle),
                            'has-error': tabHasError(tab.handle),
                        }"
                        role="tab"
                        :id="tabId(tab.handle)"
                        :aria-controls="tabPanelId(tab.handle)"
                        :aria-selected="isActive(tab.handle)"
                        :tabindex="isActive(tab.handle) ? 0 : -1"
                        @click="setActive(tab.handle)"
                        v-text="__(tab.display || `${tab.handle[0].toUpperCase()}${tab.handle.slice(1)}`)"
                    />
                </div>
                <div class="fade-left" v-if="canScrollLeft" />
                <div class="fade-right" :class="{ 'mr-8': showHiddenTabsDropdown }" v-if="canScrollRight" />

                <dropdown-list class="ltr:ml-2 rtl:mr-2" v-cloak v-if="showHiddenTabsDropdown">
                    <dropdown-item
                        v-for="(tab, index) in mainTabs"
                        v-show="shouldShowInDropdown(index)"
                        :key="tab.handle"
                        :text="__(tab.display || `${tab.handle[0].toUpperCase()}${tab.handle.slice(1)}`)"
                        @click.prevent="setActive(tab.handle)"
                    />
                </dropdown-list>
            </div>

            <!-- Main and Sidebar -->
            <div class="publish-tab-outer">
                <!-- Main -->
                <div ref="publishTabWrapper" class="publish-tab-wrapper w-full min-w-0">
                    <div
                        class="publish-tab tab-panel w-full"
                        :class="showTabs"
                        :role="showTabs && 'tabpanel'"
                        :id="showTabs && tabPanelId(tab.handle)"
                        :aria-labelledby="showTabs && tabId(tab.handle)"
                        :data-tab-handle="tab.handle"
                        tabindex="0"
                        :key="tab.handle"
                        v-for="tab in mainTabs"
                        v-show="isActive(tab.handle)"
                    >
                        <publish-sections
                            :sections="tab.sections"
                            :read-only="readOnly"
                            :syncable="syncable"
                            @updated="(handle, value) => $emit('updated', handle, value)"
                            @meta-updated="(handle, value) => $emit('meta-updated', handle, value)"
                            @synced="$emit('synced', $event)"
                            @desynced="$emit('desynced', $event)"
                            @focus="$emit('focus', $event)"
                            @blur="$emit('blur', $event)"
                        />
                    </div>
                </div>

                <!-- Sidebar(ish) -->
                <div :class="{ 'publish-sidebar': shouldShowSidebar }">
                    <div class="publish-tab">
                        <div class="publish-tab-actions" :class="{ 'as-sidebar': shouldShowSidebar }">
                            <v-portal :to="actionsPortal" :disabled="shouldShowSidebar">
                                <slot name="actions" :should-show-sidebar="shouldShowSidebar" />
                            </v-portal>
                        </div>

                        <publish-sections
                            v-if="layoutReady && shouldShowSidebar && sidebarTab"
                            :sections="sidebarTab.sections"
                            :read-only="readOnly"
                            :syncable="syncable"
                            @updated="(handle, value) => $emit('updated', handle, value)"
                            @meta-updated="(handle, value) => $emit('meta-updated', handle, value)"
                            @synced="$emit('synced', $event)"
                            @desynced="$emit('desynced', $event)"
                            @focus="$emit('focus', $event)"
                            @blur="$emit('blur', $event)"
                        />
                    </div>
                </div>
            </div>

            <div class="publish-tab publish-tab-actions-footer">
                <portal-target :name="actionsPortal" />
            </div>
        </div>
    </element-container>
</template>

<script>
import { ValidatesFieldConditions } from '../field-conditions/FieldConditions.js';

export default {
    inject: ['storeName', 'publishContainer'],

    mixins: [ValidatesFieldConditions],

    props: {
        readOnly: Boolean,
        syncable: Boolean,
        enableSidebar: {
            type: Boolean,
            default: true,
        },
    },

    data() {
        return {
            active: this.publishContainer.store.blueprint.tabs[0].handle,
            layoutReady: false,
            shouldShowSidebar: false,
            hiddenTabs: [],
            tabsAreScrolled: false,
            canScrollLeft: false,
            canScrollRight: false,
        };
    },

    computed: {
        state() {
            return this.publishContainer.store;
        },

        tabs() {
            return this.state.blueprint.tabs.filter((tab) => this.tabHasVisibleFields(tab));
        },

        inStack() {
            return this.actionsPortal !== 'publish-actions-base';
        },

        mainTabs() {
            if (this.layoutReady && !this.shouldShowSidebar) return this.tabs;

            return this.tabs.filter((tab) => tab.handle !== 'sidebar');
        },

        sidebarTab() {
            return this.tabs.find((tab) => tab.handle === 'sidebar');
        },

        numberOfTabs() {
            return this.mainTabs.length;
        },

        showTabs() {
            return this.layoutReady && this.numberOfTabs > 1;
        },

        showHiddenTabsDropdown() {
            return this.hiddenTabs.length > 0;
        },

        errors() {
            return this.state.errors;
        },

        tabsWithErrors() {
            let fields = {};
            Object.values(this.tabs).forEach((tab) => {
                tab.sections.forEach((section) => {
                    section.fields.forEach((field) => {
                        fields[field.handle] = tab.handle;
                    });
                });
            });

            let tabs = Object.keys(this.errors)
                .map((handle) => handle.split('.')[0])
                .filter((handle) => fields[handle])
                .map((handle) => fields[handle]);

            return [...new Set(tabs)];
        },

        actionsPortal() {
            return `publish-actions-${this.storeName}`;
        },

        values() {
            return this.state.values;
        },

        extraValues() {
            return this.state.extraValues;
        },
    },

    beforeUpdate() {
        if (this.shouldShowSidebar && this.active === 'sidebar') {
            this.active = this.tabAt(0);
        }
    },

    watch: {
        layoutReady(ready) {
            if (ready) {
                this.$nextTick(() => this.setActiveTabFromHash());
            }
        },
    },

    methods: {
        tabHasError(handle) {
            return this.tabsWithErrors.includes(handle);
        },

        tabHasVisibleFields(tab) {
            let visibleFields = 0;

            tab.sections.forEach((section) => {
                section.fields.forEach((field) => {
                    if (this.showField(field)) visibleFields++;
                });
            });

            return visibleFields > 0;
        },

        setActive(handle) {
            this.active = handle;

            if (!this.inStack) {
                window.location.hash = handle;
            }

            const tab = this.getTabNode(handle);

            if (!tab) {
                console.error(`Tab '${handle}' not found`);
                return;
            }

            this.scrollTabIntoView(tab);

            tab.focus();

            this.$events.$emit('tab-switched', handle);
        },

        isActive(handle) {
            return handle === this.active;
        },

        tabIndex(handle) {
            return this.mainTabs.findIndex((tab) => tab.handle === (handle || this.active));
        },

        tabAt(index) {
            const tab = this.mainTabs[index];

            return tab ? tab.handle : undefined;
        },

        setActiveTabFromHash() {
            if (this.inStack) return;

            if (window.location.hash.length === 0) return;

            const handle = window.location.hash.substr(1);

            const index = this.tabIndex(handle);

            if (index >= 0) {
                this.setActive(handle);
            } else {
                window.location.hash = '';
            }
        },

        activateNextTab() {
            this.activateTabAt((this.tabIndex() + 1) % this.numberOfTabs);
        },

        activatePreviousTab() {
            this.activateTabAt((this.tabIndex() - 1 + this.numberOfTabs) % this.numberOfTabs);
        },

        activateFirstTab() {
            this.activateTabAt(0);
        },

        activateLastTab() {
            this.activateTabAt(this.numberOfTabs - 1);
        },

        activateTabAt(index) {
            this.setActive(this.tabAt(index));
        },

        tabId(handle) {
            return `${this.pascalCase(handle)}Tab`;
        },

        tabPanelId(handle) {
            return `${this.pascalCase(handle)}TabPanel`;
        },

        pascalCase(handle) {
            return handle
                .split('_')
                .map((word) => word.slice(0, 1).toUpperCase() + word.slice(1))
                .join('');
        },

        getTabNode(handle) {
            return this.$refs.tab[this.tabIndex(handle)];
        },

        scrollTabs(event) {
            if (!this.$refs.tabs) return;

            this.$refs.tabs.scrollLeft += event.deltaY;

            this.updateHiddenTabs();
        },

        scrollTabIntoView(tab) {
            if (typeof tab === 'string') {
                tab = this.getTabNode(tab);
            }
            if (!tab) {
                console.error(`Tab '${tab}' not found`);
                return;
            }

            const side = this.tabIsOutsideView(tab);
            if (!side) {
                return;
            }

            const offset = 20; // Always show a small part of the next tab

            if (side === 'left') {
                this.$refs.tabs.scrollLeft = tab.offsetLeft - offset;
            } else {
                this.$refs.tabs.scrollLeft =
                    tab.offsetLeft + tab.offsetWidth - this.$refs.tabs.clientWidth + offset + 8;
            }

            this.updateHiddenTabs();
        },

        updateScrollHints() {
            this.canScrollLeft = this.$refs.tabs && this.$refs.tabs.scrollLeft > 0;
            this.canScrollRight =
                this.$refs.tabs &&
                this.$refs.tabs.scrollLeft < this.$refs.tabs.scrollWidth - this.$refs.tabs.clientWidth;
        },

        containerWasResized($event) {
            const { width } = $event;

            // NOTE Using computed properties for these will cause a lot of unnecessary re-renders
            this.layoutReady = width !== null;
            this.shouldShowSidebar = this.enableSidebar && width > 920;

            if (!this.layoutReady) return;

            this.$nextTick(() => {
                this.updateScrollHints();
                this.updateHiddenTabs();
            });
        },

        updateHiddenTabs() {
            if (!this.$refs.tabs) return;

            const hidden = [];

            this.$refs.tab.forEach((tab, index) => {
                if (this.tabIsOutsideView(tab, 20)) {
                    hidden.push(index);
                }
            });

            if (JSON.stringify(hidden) !== JSON.stringify(this.hiddenTabs)) {
                this.hiddenTabs = hidden;
            }
        },

        tabIsOutsideView(tab, tolerance = 0) {
            const viewportRect = this.$refs.tabs.getBoundingClientRect();
            const tabRect = tab.getBoundingClientRect();

            if (viewportRect.left - tabRect.left > tolerance) {
                return 'left';
            }

            if (tabRect.right - viewportRect.right > tolerance) {
                return 'right';
            }

            return false;
        },

        shouldShowInDropdown(index) {
            return this.hiddenTabs.includes(index);
        },
    },
};
</script>
