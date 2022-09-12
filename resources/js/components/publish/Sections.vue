<template>

    <element-container @resized="containerWasResized">
    <div>

        <!-- Tabs -->
        <div v-if="showTabs" class="tabs-container flex items-center" :class="{ 'offset-for-sidebar': shouldShowSidebar }">
            <div
                class="publish-tabs tabs flex-shrink"
                ref="tabs"
                role="tablist"
                :aria-label="__('Edit Content')"
                @keydown.arrow-left="activatePreviousTab"
                @keydown.arrow-right="activateNextTab"
                @keydown.arrow-up="activatePreviousTab"
                @keydown.arrow-down="activateNextTab"
                @keydown.home="activateFirstTab"
                @keydown.end="activateLastTab"       
            >
                <button v-for="(section, index) in mainSections"
                    class="tab-button"
                    :key="section.handle"
                    :class="{
                        'active': isActive(section.handle),
                        'has-error': sectionHasError(section.handle),
                        'invisible': isTabHidden(index)
                    }"
                    role="tab"
                    :id="tabId(section.handle)"
                    :aria-controls="tabPanelId(section.handle)"
                    :aria-selected="isActive(section.handle)"
                    :tabindex="isActive(section.handle) ? 0 : -1"
                    @click="setActive(section.handle)"
                    v-text="section.display || `${section.handle[0].toUpperCase()}${section.handle.slice(1)}`"
                ></button>
            </div>
            <dropdown-list class="ml-1" v-cloak v-if="showHiddenTabsDropdown">
                <dropdown-item
                    v-for="(section, index) in mainSections"
                    v-show="isTabHidden(index)"
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
                    class="publish-section w-full"
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
            visibleTabs: 0,
            layoutReady: false,
            shouldShowSidebar: false,
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
            return this.numberOfTabs > this.visibleTabs;
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

    mounted() {
        this.setActiveTabFromHash();
    },

    beforeUpdate() {
        if (this.shouldShowSidebar && this.active === 'sidebar') {
            this.active = this.state.blueprint.sections[0].handle
        }
    },

    methods: {

        sectionHasError(handle) {
            return this.sectionsWithErrors.includes(handle);
        },

        setActive(tab) {
            this.active = tab;
            this.$events.$emit('tab-switched', tab);

            if (! this.inStack) {
                window.location.hash = tab;
            }

            this.$refs.tabs.childNodes[this.tabIndex(tab)].focus();
        },

        isActive(tab) {
            return tab === this.active;
        },

        tabIndex(tab) {
            return this.mainSections.findIndex(section => section.handle === (tab || this.active));
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

            if (index >= 0 && index < this.visibleTabs) {
                this.setActive(handle);
            } else {
                window.location.hash = '';
            }
        },

        activateNextTab() {
            this.activateTabAt((this.tabIndex() + 1) % this.visibleTabs);
        },

        activatePreviousTab() {
            this.activateTabAt((this.tabIndex() - 1 + this.visibleTabs) % this.visibleTabs);
        },

        activateFirstTab() {
            this.activateTabAt(0);
        },

        activateLastTab() {
            this.activateTabAt(this.visibleTabs - 1);
        },

        activateTabAt(index) {
            this.setActive(this.tabAt(index));
        },

        tabId(handle) {
            return `${this.camelCase(handle)}Tab`;
        },

        tabPanelId(handle) {
            return `${this.camelCase(handle)}TabPanel`;
        },

        camelCase(handle) {
            return handle
                .split('_')
                .map(word => word.slice(0, 1).toUpperCase() + word.slice(1))
                .join('');
        },

        containerWasResized($event) {
            const { width } = $event;

            // NOTE Using computed properties for these will cause a lot of unnecessary re-renders
            this.layoutReady = (width !== null);
            this.shouldShowSidebar = (this.enableSidebar && width > 920);

            if (this.layoutReady) {
                this.wangjangleTabVisibility();
            }
        },

        wangjangleTabVisibility: _.debounce(function () {
            this.$nextTick(() => {
                if (!this.$refs.tabs) return;

                let visibleTabs = 0;

                // Leave 40px for the dropdown list.
                const maxWidth = this.$refs.publishSectionWrapper.offsetWidth - 40;
                let tabWidthSum = 0;

                this.$refs.tabs.childNodes.forEach((tab, index) => {
                    tabWidthSum += tab.offsetWidth;

                    if (tabWidthSum < maxWidth) {
                        visibleTabs += 1;
                    }
                })

                this.visibleTabs = visibleTabs;
            });
        }, 100),

        isTabHidden(index) {
            return index >= this.visibleTabs;
        }
    }

}
</script>
