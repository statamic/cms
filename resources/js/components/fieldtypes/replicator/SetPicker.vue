<template>
    <template v-if="!hasMultipleSets">
        <Primitive @click="singleButtonClicked">
            <slot name="trigger" />
        </Primitive>
    </template>

    <ui-popover
        v-else
        inset
        class="set-picker select-none w-[300px]"
        :open="isOpen"
        @update:open="isOpen = $event"
        @clicked-away="$emit('clicked-away', $event)"
    >
        <template #trigger>
            <slot name="trigger" />
        </template>
        <template #default>
            <div class="set-picker-header flex items-center border-b p-3 text-xs dark:border-gray-600">
                <ui-input
                    ref="search"
                    size="sm"
                    type="text"
                    :placeholder="__('Search Sets')"
                    icon-prepend="magnifying-glass"
                    v-show="showSearch"
                    v-model="search"
                    data-set-picker-search-input
                />
                <div v-if="showGroupBreadcrumb" class="flex items-center font-medium text-gray-700 dark:text-gray-300 gap-1">
                    <button @click="unselectGroup" class="hover:text-gray-900 dark:hover:text-white">
                        {{ __('Groups') }}
                    </button>
                    <ui-icon name="ui/chevron-right" class="size-4" />
                    <span>{{ selectedGroupDisplayText }}</span>
                </div>
            </div>
            <div class="max-h-[21rem] overflow-auto p-1">
                <div
                    v-for="(item, i) in items"
                    :key="item.handle"
                    class="cursor-pointer rounded-md"
                    :class="{ 'bg-gray-100 dark:bg-gray-900': selectionIndex === i }"
                    @mouseover="selectionIndex = i"
                    :title="__(item.instructions)"
                >
                    <div v-if="item.type === 'group'" @click="selectGroup(item.handle)" class="group flex rounded-md px-2 py-1.5 gap-3">
                        <ui-icon
                            :name="groupIconName(item.icon)"
                            class="size-9 rounded-md border border-gray-300 bg-white dark:bg-gray-900/50 dark:border-gray-600 shadow-ui-xs p-2"
                        />
                        <div class="flex-1">
                            <div class="w-50 line-clamp-2 text-sm font-medium text-gray-900 dark:text-dark-175">
                                {{ __(item.display || item.handle) }}
                            </div>
                            <div v-if="item.instructions" class="w-50 line-clamp-2 text-2xs text-gray-700 dark:text-dark-175">
                                {{ __(item.instructions) }}
                            </div>
                        </div>
                        <ui-icon name="ui/chevron-right" class="me-2" />
                    </div>
                    <div v-if="item.type === 'set'" @click="addSet(item.handle)" class="group flex rounded-md px-2 py-1.5 gap-3">
                        <ui-icon
                            :name="setIconName(item.icon)"
                            :directory="iconBaseDirectory"
                            class="size-9 rounded-md border border-gray-300 bg-white dark:bg-gray-900/50 dark:border-gray-600 shadow-ui-xs p-2"
                        />
                        <div class="flex-1">
                            <div class="w-52 line-clamp-2 text-sm font-medium text-gray-900 dark:text-dark-175">
                                {{ __(item.display || item.handle) }}
                            </div>
                            <div v-if="item.instructions" class="w-52 truncate text-2xs text-gray-700 dark:text-dark-175">
                                {{ __(item.instructions) }}
                            </div>
                        </div>
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
    },

    data() {
        return {
            selectedGroupHandle: null,
            search: null,
            selectionIndex: 0,
            keybindings: [],
            isOpen: false,
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

        iconBaseDirectory() {
            return this.$config.get('setIconsDirectory');
        },

        iconSubFolder() {
            return this.$config.get('setIconsFolder');
        },

        iconDirectory() {
            let iconDirectory = this.$config.get('setIconsDirectory');
            let iconFolder = this.$config.get('setIconsFolder');

            if (iconFolder) {
                iconDirectory = iconDirectory + '/' + iconFolder;
            }

            return iconDirectory;
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
            this.selectionIndex = this.selectionIndex === 0 ? this.items.length - 1 : this.selectionIndex - 1;
        },

        keypressDown(e) {
            e.preventDefault();
            this.selectionIndex = this.selectionIndex === this.items.length - 1 ? 0 : this.selectionIndex + 1;
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
            const item = this.items[this.selectionIndex];
            if (item.type === 'group') {
                this.selectGroup(item.handle);
            } else {
                this.addSet(item.handle);
            }
        },

        singleButtonClicked() {
            this.addSet(this.sets[0].sets[0].handle);
        },

        groupIconName(name) {
            if (!name) return 'folder';

            return this.iconSubFolder ? this.iconSubFolder + '/' + name : name;
        },

        setIconName(name) {
            if (!name) return 'plus';

            return this.iconSubFolder ? this.iconSubFolder + '/' + name : name;
        },

        open() {
            this.isOpen = true;
        },
    },
};
</script>
