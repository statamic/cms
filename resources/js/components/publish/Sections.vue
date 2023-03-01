<template>

    <element-container @resized="containerWasResized">
    <div>

        <!-- Tabs -->
        <div 
            v-if="showTabs"
            class="tabs-container flex items-center"
            :class="{ 'offset-for-sidebar': shouldShowSidebar }"
        >
            <div
                class="publish-tabs tabs flex-shrink"
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
                <button v-for="section in mainSections"
                    class="tab-button"
                    :ref="section.handle + '-tab'"
                    :key="section.handle"
                    :class="{
                        'active': isActive(section.handle),
                        'has-error': sectionHasError(section.handle),
                    }"
                    role="tab"
                    :id="tabId(section.handle)"
                    :aria-controls="tabPanelId(section.handle)"
                    :aria-selected="isActive(section.handle)"
                    :tabindex="isActive(section.handle) ? 0 : -1"
                    @click="setActive(section.handle)"
                    v-text="section.display || `${section.handle[0].toUpperCase()}${section.handle.slice(1)}`"
                />
            </div>

            <div class="fade-left" v-if="canScrollLeft" />
            <div class="fade-right" :class="{ 'mr-4': showHiddenTabsDropdown }" v-if="canScrollRight" />

            <dropdown-list class="ml-1" v-cloak v-if="showHiddenTabsDropdown">
                <dropdown-item
                    v-for="(section, index) in mainSections"
                    v-show="shouldShowInDropdown(index)"
                    :key="section.handle"
                    :text="section.display || `${section.handle[0].toUpperCase()}${section.handle.slice(1)}`"
                    @click.prevent="setActive(section.handle)"
                />
            </dropdown-list>
        </div>

        <!-- Main and Sidebar -->
        <div class="flex justify-between">

            <!-- Main -->
            <div ref="publishSectionWrapper" class="publish-section-wrapper w-full min-w-0">
                <div
                    class="publish-section tab-panel w-full"
                    :class="showTabs && 'rounded-tl-none'"
                    :role="showTabs && 'tabpanel'"
                    :id="showTabs && tabPanelId(section.handle)"
                    :aria-labelledby="showTabs && tabId(section.handle)"
                    tabindex="0"
                    :key="section.handle"
                    v-for="section in mainSections"
                    v-show="isActive(section.handle)"
                >
                    <publish-fields
                        :fields="section.fields"
                        :read-only="readOnly"
                        :syncable="syncable"
                        :can-toggle-labels="canToggleLabels"
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
                <div class="publish-section">
                    <div class="publish-section-actions" :class="{ 'as-sidebar': shouldShowSidebar }">
                        <portal :to="actionsPortal" :disabled="shouldShowSidebar">
                            <slot name="actions" :should-show-sidebar="shouldShowSidebar" />
                        </portal>
                    </div>

                    <publish-fields
                        v-if="layoutReady && shouldShowSidebar && sidebarSection"
                        :fields="sidebarSection.fields"
                        :read-only="readOnly"
                        :syncable="syncable"
                        :can-toggle-labels="canToggleLabels"
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

        <portal-target :name="actionsPortal" class="publish-section publish-section-actions-footer" />

    </div>
    </element-container>

</template>

<script>
export default {

    inject: ['storeName'],

    props: {
        readOnly: Boolean,
        syncable: Boolean,
        canToggleLabels: Boolean,
        enableSidebar: {
            type: Boolean,
            default: true
        }
    },

    data() {
        const state = this.$store.state.publish[this.storeName];

        return {
            active: state.blueprint.sections[0].handle,
            layoutReady: false,
            shouldShowSidebar: false,
            hiddenTabs: [],
            tabsAreScrolled: false,
            canScrollLeft: false,
            canScrollRight: false,
        }
    },

    computed: {

        state() {
            return this.$store.state.publish[this.storeName];
        },

        sections() {
            return this.state.blueprint.sections;
        },

        inStack() {
            return this.actionsPortal !== 'publish-actions-base';
        },

        mainSections() {
            if (this.layoutReady && ! this.shouldShowSidebar) return this.sections;

            return this.sections.filter(section => section.handle !== 'sidebar');
        },

        sidebarSection() {
            return this.sections.find(section => section.handle === 'sidebar');
        },

        numberOfTabs() {
            return this.mainSections.length;
        },

        showTabs() {
            return this.layoutReady && this.numberOfTabs > 1
        },

        showHiddenTabsDropdown() {
            return this.hiddenTabs.length > 0;
        },

        errors() {
            return this.state.errors;
        },

        sectionsWithErrors() {
            const handles = Object.keys(this.errors).map((fieldHandle) => {
                const topFieldHandle = fieldHandle.split('.')[0];
                const section = this.sections.find(section =>
                    section.fields.some(field => field.handle === topFieldHandle)
                );

                return section && section.handle
            });

            return _.uniq(handles);
        },

        actionsPortal() {
            return `publish-actions-${this.storeName}`;
        }

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
        }

    },

    methods: {

        sectionHasError(handle) {
            return this.sectionsWithErrors.includes(handle);
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
            return this.mainSections.findIndex(section => section.handle === (handle || this.active));
        },

        tabAt(index) {
            const section = this.mainSections[index];

            return section ? section.handle : undefined;
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
                .map(word => word.slice(0, 1).toUpperCase() + word.slice(1))
                .join('');
        },

        getTabNode(handle) {
            return this.$refs.tabs.childNodes[this.tabIndex(handle)];
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
            } 
            else {
                this.$refs.tabs.scrollLeft = tab.offsetLeft + tab.offsetWidth - this.$refs.tabs.clientWidth + offset + 8;
            }

            this.updateHiddenTabs();
        },

        updateScrollHints() {
            this.canScrollLeft = this.$refs.tabs && (this.$refs.tabs.scrollLeft > 0);
            this.canScrollRight = this.$refs.tabs && (this.$refs.tabs.scrollLeft < (this.$refs.tabs.scrollWidth - this.$refs.tabs.clientWidth));
        },

        containerWasResized($event) {
            const { width } = $event;

            // NOTE Using computed properties for these will cause a lot of unnecessary re-renders
            this.layoutReady = (width !== null);
            this.shouldShowSidebar = (this.enableSidebar && width > 920);

            if (!this.layoutReady) return;
                
            this.$nextTick(() => {
                this.updateScrollHints();
                this.updateHiddenTabs();
            });
        },

        updateHiddenTabs() {
            if (!this.$refs.tabs) return;

            const hidden = [];

            this.$refs.tabs.childNodes.forEach((tab, index) => {
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

            if ((viewportRect.left - tabRect.left) > tolerance) {
                return 'left';
            } 
            
            if ((tabRect.right - viewportRect.right) > tolerance) {
                return 'right';
            }

            return false;
        },

        shouldShowInDropdown(index) {
            return this.hiddenTabs.includes(index);
        }
    }

}
</script>
