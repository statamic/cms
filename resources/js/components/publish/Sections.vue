<template>

    <element-container @resized="containerWasResized">
    <div>
        <div class="tabs-container flex items-center" :class="{ 'offset-for-sidebar': shouldShowSidebar }">
            <div
                class="publish-tabs tabs flex-shrink" v-show="mainSections.length > 1"
                ref="tabs"
                role="tablist"
                :aria-label="__('Edit Content')"
            >
                <button v-for="(section, index) in mainSections"
                    class="tab-button"
                    :ref="section.handle + '-tab'"
                    :key="section.handle"
                    :class="{
                        'active': section.handle == active,
                        'has-error': sectionHasError(section.handle),
                        'invisible': isTabHidden(index)
                    }"
                    :aria-controls="section.handle + '-tab'"
                    :aria-selected="section.handle == active ? true : false"
                    :id="section.handle + '-tab-control'"
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

        <div class="flex justify-between">
            <div ref="publishSectionWrapper" class="publish-section-wrapper w-full">
                <div
                    role="tabpanel"
                    class="publish-section w-full"
                    :aria-labeledby="section.display"
                    :id="section.handle + '-tab'"
                    :class="{ 'rounded-tl-none' : mainSections.length > 1 }"
                    :key="section.handle"
                    v-for="section in mainSections"
                    v-show="section.handle === active"
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

            <div :class="{ 'publish-sidebar': shouldShowSidebar }">
                <div class="publish-section">
                    <div class="publish-section-actions" :class="{ 'as-sidebar': shouldShowSidebar }">
                        <portal :to="actionsPortal" :disabled="shouldShowSidebar">
                            <slot name="actions" :should-show-sidebar="shouldShowSidebar" />
                        </portal>
                    </div>

                    <publish-fields
                        v-if="shouldShowSidebar && sidebarSection"
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
            containerWidth: null,
            visibleTabs: []
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
            if (! this.shouldShowSidebar) return this.sections;

            if (this.active === "sidebar") {
                this.active = this.state.blueprint.sections[0].handle
            }

            return _.filter(this.sections, section => section.handle != 'sidebar');
        },

        sidebarSection() {
            return _.find(this.sections, { handle: 'sidebar' });
        },

        shouldShowSidebar() {
            return this.enableSidebar && this.containerWidth > 920;
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
        },

        showHiddenTabsDropdown() {
            return this.mainSections.length > this.visibleTabs.length;
        }

    },

    mounted() {
        if (this.inStack) return;

        // Deep linking/refreshing to a specific #section
        if (window.location.hash.length > 0) {
            let hash = window.location.hash.substr(1);
            // if hash is in this.visibleTabs, make it active.
            if (_.chain(this.visibleTabs).values().contains(hash)) {
                this.setActive(hash);
            } else {
                window.location.hash = '';
            }
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
        },

        isTabHidden(section) {
            return false
        },

        containerWasResized($event) {
            this.containerWidth = $event.width;
            this.wangjangleTabVisibility();
        },

        wangjangleTabVisibility: _.debounce(function () {
            this.$nextTick(() => {
                let visibleTabs = []

                // Offset 40px for dropdown list position
                let maxWidth = this.$refs.publishSectionWrapper.offsetWidth - 40;
                let tabWidthSum = 0;

                this.$refs.tabs.childNodes.forEach((tab, index) => {
                    tabWidthSum += tab.offsetWidth;

                    if (tabWidthSum < maxWidth) {
                        visibleTabs.push(this.mainSections[index].handle);
                    }
                })

                this.visibleTabs = visibleTabs;
            });
        }, 100),

        isTabVisible(index) {
            return _.contains(this.visibleTabs, this.mainSections[index].handle);
        },

        isTabHidden(index) {
            return ! _.contains(this.visibleTabs, this.mainSections[index].handle);
        }
    }

}
</script>
