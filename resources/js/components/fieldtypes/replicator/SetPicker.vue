<template>
    <template v-if="!hasMultipleSets">
        <Primitive @click="singleButtonClicked">
            <slot name="trigger" />
        </Primitive>
    </template>

    <!-- Modal for Grid Mode -->
    <ui-modal
        :blur="false"
        :title="__('Add Set')"
        v-model:open="isOpen"
        v-else-if="shouldUseModal"
        class="xl:max-w-3xl 2xl:max-w-5xl"
    >
        <template #trigger>
            <slot name="trigger" />
        </template>

        <template #default>
            <div class="flex items-center p-1.5 gap-1.5">
                <ui-input
                    :placeholder="__('Search Sets')"
                    class="[&_svg]:size-5"
                    input-attrs="data-set-picker-search-input"
                    icon-prepend="magnifying-glass"
                    ref="search"
                    size="sm"
                    type="text"
                    v-model="search"
                    :variant="mode === 'list' ? 'ghost' : 'default'"
                />

                <ui-toggle-group v-model="mode" size="sm">
                    <ui-toggle-item icon="layout-list" value="list" :aria-label="__('List view')" />
                    <ui-toggle-item icon="layout-grid" value="grid" :aria-label="__('Grid view')" />
                </ui-toggle-group>
            </div>

            <!-- Tabs for Grid Mode -->
            <ui-tabs default-tab="all" v-model="selectedTab" class="w-full" v-if="mode === 'grid'">
                <ui-tab-list class="px-2">
                    <ui-tab-trigger :text="group.display" :name="group.handle" v-for="group in groupedItems" :key="group.handle" />
                </ui-tab-list>
                <ui-tab-content :name="group.handle" v-for="group in groupedItems" :key="group.handle">
                    <div class="p-3 grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div
                            v-for="(item, i) in group.items"
                            :key="item.handle"
                            class="cursor-pointer rounded-lg"
                            :class="{ 'bg-gray-100 dark:bg-gray-900': selectionIndex === i }"
                            @mouseover="selectionIndex = i"
                            :title="__(item.instructions)"
                        >
                            <div @click="addSet(item.handle)" class="p-2.5">
                                <div class="h-40 w-full flex items-center justify-center">
                                    <img :src="item.image" class="rounded-lg h-40 object-contain bg-gray-50 dark:bg-gray-850" v-if="item.image" />
                                    <ui-icon :name="item.icon || 'add-section'" :set="iconSet" class="size-8 text-gray-600 dark:text-gray-300" v-else />
                                </div>
                                <div class="line-clamp-1 text-base mt-1 text-center text-gray-900 dark:text-gray-200">
                                    {{ __(item.display || item.handle) }}
                                </div>
                                <ui-description v-if="item.instructions" class="text-center mb-2">
                                    {{ __(item.instructions) }}
                                </ui-description>
                            </div>
                        </div>
                        <div v-if="group.items.length === 0" class="p-3 text-center text-xs text-gray-600">
                            {{ search ? __('No results') : __('No sets available') }}
                        </div>
                    </div>
                </ui-tab-content>
            </ui-tabs>
        </template>
    </ui-modal>

    <!-- Use Popover for list mode when content fits -->
    <ui-popover
        v-else
        :align="align"
        :open="isOpen"
        @clicked-away="$emit('clicked-away', $event)"
        @update:open="isOpen = $event"
        class="set-picker select-none w-72"
        inset
    >
        <template #trigger>
            <slot name="trigger" />
        </template>

        <template #default>
            <!-- Popover content with toggle group -->
            <div class="flex items-center border-b border-gray-200 dark:border-gray-600 p-1.5 gap-1.5">
                <ui-input
                    :placeholder="__('Search Sets')"
                    class="[&_svg]:size-5"
                    input-attrs="data-set-picker-search-input"
                    icon-prepend="magnifying-glass"
                    ref="search"
                    size="sm"
                    type="text"
                    v-model="search"
                    variant="ghost"
                />

                <ui-toggle-group v-model="mode" size="sm">
                    <ui-toggle-item icon="layout-list" value="list" aria-label="List view" />
                    <ui-toggle-item icon="layout-grid" value="grid" aria-label="Grid view" />
                </ui-toggle-group>
            </div>

            <!-- Breadcrumbs for List Mode -->
            <div v-if="showGroupBreadcrumb" class="flex items-center p-1.5 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-600">
                <ui-button @click="unselectGroup" size="xs" variant="ghost">
                    {{ __('Groups') }}
                </ui-button>
                <ui-icon name="chevron-right" class="size-3! mt-[1px]" />
                <span class="text-gray-700 dark:text-gray-300 text-xs px-2">
                    {{ selectedGroupDisplayText }}
                </span>
            </div>

            <!-- List Mode -->
            <div class="max-h-[21rem] overflow-auto p-1.5">
                <div
                    v-for="(item, i) in items"
                    :key="item.handle"
                    class="cursor-pointer rounded-lg"
                    :class="{ 'bg-gray-100 dark:bg-gray-900': selectionIndex === i }"
                    @mouseover="selectionIndex = i"
                    :title="__(item.instructions)"
                >
                    <div v-if="item.type === 'group'" @click="selectGroup(item.handle)" class="group flex items-center rounded-lg p-2 gap-2 sm:gap-3">
                        <ui-icon :name="item.icon || 'folder'" :set="iconSet" class="size-4 text-gray-600 dark:text-gray-300" />
                        <div class="flex-1">
                            <div class="line-clamp-1 text-sm text-gray-900 dark:text-gray-200">
                                {{ __(item.display || item.handle) }}
                            </div>
                            <ui-description v-if="item.instructions" class="w-48 truncate text-2xs">
                                {{ __(item.instructions) }}
                            </ui-description>
                        </div>
                        <ui-icon name="chevron-right" class="me-1 size-2" />
                    </div>
                    <div v-if="item.type === 'set'" @click="addSet(item.handle)" class="group flex items-center rounded-xl p-2.5 gap-2 sm:gap-3">
                        <ui-icon :name="item.icon || 'plus'" :set="iconSet" class="size-4 text-gray-600 dark:text-gray-300" />
                        <ui-hover-card :delay="0" :open="selectionIndex === i">
                            <template #trigger>
                                <div class="flex-1">
                                    <div class="line-clamp-1 text-sm text-gray-900 dark:text-gray-200">
                                        {{ __(item.display || item.handle) }}
                                    </div>
                                    <ui-description v-if="item.instructions" class="w-56 truncate text-2xs">
                                        {{ __(item.instructions) }}
                                    </ui-description>
                                </div>
                            </template>
                            <template #default v-if="item.image">
                                <div class="max-w-96 max-h-[calc(80vh)] screen-fit">
                                    <p v-if="item.instructions" class="text-gray-800 dark:text-gray-200 mb-2">
                                        {{ __(item.instructions) }}
                                    </p>
                                    <img :src="item.image" class="rounded-lg" />
                                </div>
                            </template>
                        </ui-hover-card>
                    </div>
                </div>
                <div v-if="noSearchResults" class="p-3 text-center text-xs text-gray-600">
                    {{ __('No results') }}
                </div>
            </div>
        </template>
    </ui-popover>
</template>

<script>
import { Primitive } from 'reka-ui';

export default {
    emits: ['added', 'clicked-away'],

    components: {
        Primitive,
    },

    props: {
        sets: Array,
        enabled: { type: Boolean, default: true },
        align: { type: String, default: 'start' },
    },

    data() {
        return {
            selectedGroupHandle: null,
            search: null,
            selectionIndex: 0,
            keybindings: [],
            isOpen: false,
            mode: this.getStoredMode(),
            selectedTab: 'all',
        };
    },

    computed: {
        showSearch() {
            return !this.hasMultipleGroups || !this.selectedGroup;
        },

        showGroupBreadcrumb() {
            return this.hasMultipleGroups && this.selectedGroup;
        },

        showGroups() {
            return this.hasMultipleGroups && !this.selectedGroup && !this.search;
        },

        hasMultipleSets() {
            return (
                this.sets.reduce((count, group) => {
                    return count + group.sets.length;
                }, 0) > 1
            );
        },

        hasMultipleGroups() {
            return this.sets.length > 1;
        },

        selectedGroup() {
            return this.sets.find((group) => group.handle === this.selectedGroupHandle);
        },

        selectedGroupDisplayText() {
            return this.selectedGroup ? __(this.selectedGroup.display || this.selectedGroup.handle) : null;
        },

        visibleSets() {
            if (!this.selectedGroup && !this.search) return [];

            let sets = this.selectedGroup
                ? this.selectedGroup.sets
                : this.sets.reduce((sets, group) => {
                      return sets.concat(group.sets);
                  }, []);

            if (this.search) {
                return sets
                    .filter((set) => !set.hide)
                    .filter((set) => {
                        return (
                            __(set.display).toLowerCase().includes(this.search.toLowerCase()) ||
                            set.handle.toLowerCase().includes(this.search.toLowerCase())
                        );
                    });
            }

            return sets.filter((set) => !set.hide);
        },

        items() {
            let items = [];

            if (this.showGroups) {
                this.sets.forEach((group) => {
                    items.push({ ...group, type: 'group' });
                });
            }

            this.visibleSets.forEach((set) => {
                items.push({ ...set, type: 'set' });
            });

            return items;
        },

        noSearchResults() {
            return this.search && this.visibleSets.length === 0;
        },

        iconSet() {
            return this.$config.get('replicatorSetIcons') || undefined;
        },

        // For Grid Mode - groups items into tabs
        groupedItems() {
            const groups = {};

            // Add all sets to 'all' group
            groups.all = {
                display: __('All'),
                handle: 'all',
                items: []
            };

            // Group sets by their parent group
            this.sets.forEach(group => {
                let filteredSets = group.sets.filter(set => !set.hide);

                // Apply search filter if there's a search term
                if (this.search) {
                    filteredSets = filteredSets.filter(set => {
                        return (
                            __(set.display).toLowerCase().includes(this.search.toLowerCase()) ||
                            set.handle.toLowerCase().includes(this.search.toLowerCase())
                        );
                    });
                }

                groups[group.handle] = {
                    display: group.display || group.handle,
                    handle: group.handle,
                    items: filteredSets
                };

                // Add filtered sets to 'all' group
                groups.all.items = groups.all.items.concat(filteredSets);
            });

            return groups;
        },

        // Get items for the currently selected tab
        currentTabItems() {
            if (this.mode !== 'grid') return [];

            const group = this.groupedItems[this.selectedTab];
            return group ? group.items : [];
        },

        // Determine whether to use Modal or Popover
        shouldUseModal() {
            // Modal for grid mode, Popover for list mode
            return this.mode === 'grid';
        },
    },

    watch: {
        isOpen(isOpen) {
            if (isOpen) {
                if (this.sets.length === 1) {
                    this.selectedGroupHandle = this.sets[0].handle;
                }
                this.bindKeys();
            } else {
                this.unbindKeys();
            }
        },
        search() {
            this.selectionIndex = 0;
        },
        selectedTab() {
            this.selectionIndex = 0;
        },
        mode() {
            this.saveMode();
        },
    },

    methods: {
        addSet(handle) {
            this.$emit('added', handle);
            this.unselectGroup();
            this.search = null;
            this.isOpen = false;
        },

        selectGroup(handle) {
            this.selectedGroupHandle = handle;
            this.selectionIndex = 0;
        },

        unselectGroup() {
            this.selectedGroupHandle = null;
        },

        bindKeys() {
            this.keybindings = [
                this.$keys.bindGlobal('up', (e) => this.keypressUp(e)),
                this.$keys.bindGlobal('down', (e) => this.keypressDown(e)),
                this.$keys.bindGlobal('enter', (e) => this.keypressEnter(e)),
                this.$keys.bindGlobal('right', (e) => this.keypressRight(e)),
                this.$keys.bindGlobal('left', (e) => this.keypressLeft(e)),
            ];
        },

        unbindKeys() {
            this.keybindings.forEach((binding) => binding.destroy());
            this.keybindings = [];
        },

        keypressUp(e) {
            e.preventDefault();
            const items = this.mode === 'grid' ? this.currentTabItems : this.items;
            this.selectionIndex = this.selectionIndex === 0 ? items.length - 1 : this.selectionIndex - 1;
        },

        keypressDown(e) {
            e.preventDefault();
            const items = this.mode === 'grid' ? this.currentTabItems : this.items;
            this.selectionIndex = this.selectionIndex === items.length - 1 ? 0 : this.selectionIndex + 1;
        },

        keypressRight(e) {
            e.preventDefault();
            if (this.selectedGroup || this.search) return; // Pressing right to select a set feels awkward.
            this.keypressEnter(e);
        },

        keypressLeft(e) {
            e.preventDefault();
            this.unselectGroup();
        },

        keypressEnter(e) {
            e.preventDefault();
            const items = this.mode === 'grid' ? this.currentTabItems : this.items;
            const item = items[this.selectionIndex];
            if (item && item.type === 'group') {
                this.selectGroup(item.handle);
            } else if (item) {
                this.addSet(item.handle);
            }
        },

        singleButtonClicked() {
            this.addSet(this.sets[0].sets[0].handle);
        },

        open() {
            this.isOpen = true;
        },

        getStoredMode() {
            try {
                return localStorage.getItem('statamic.replicator.setPicker.mode') || 'list';
            } catch (e) {
                return 'list';
            }
        },

        saveMode() {
            try {
                localStorage.setItem('statamic.replicator.setPicker.mode', this.mode);
            } catch (e) {
                // Ignore localStorage errors
            }
        },
    },
};
</script>
