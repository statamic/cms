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
            <div class="set-picker-header flex items-center border-b p-3 text-xs dark:border-dark-900">
                <ui-input
                    ref="search"
                    size="sm"
                    type="text"
                    :placeholder="__('Search Sets')"
                    v-show="showSearch"
                    v-model="search"
                />
                <div v-if="showGroupBreadcrumb" class="flex items-center font-medium text-gray-700 dark:text-gray-600">
                    <button
                        @click="unselectGroup"
                        class="rounded-sm hover:text-gray-900 dark:hover:text-gray-500 ltr:ml-2.5 rtl:mr-2.5"
                    >
                        {{ __('Groups') }}
                    </button>
                    <ui-icon name="micro/chevron-right" class="h-4 w-4" />
                    <span>{{ selectedGroupDisplayText }}</span>
                </div>
            </div>
            <div class="max-h-[21rem] overflow-auto p-1">
                <div
                    v-for="(item, i) in items"
                    :key="item.handle"
                    class="cursor-pointer rounded-sm"
                    :class="{ 'bg-gray-100 dark:bg-dark-600': selectionIndex === i }"
                    @mouseover="selectionIndex = i"
                >
                    <div
                        v-if="item.type === 'group'"
                        @click="selectGroup(item.handle)"
                        class="group flex items-center rounded-md px-2 py-1.5"
                    >
                        <ui-icon
                            :name="groupIconName(item.icon)"
                            :directory="iconBaseDirectory"
                            class="h-9 w-9 rounded-sm border border-gray-600 bg-white p-2 text-gray-800 dark:border-dark-800 dark:bg-dark-650 dark:text-dark-175 ltr:mr-2 rtl:ml-2"
                        />
                        <div class="flex-1">
                            <div class="w-52 truncate text-sm font-medium text-gray-800 dark:text-dark-175">
                                {{ __(item.display || item.handle) }}
                            </div>
                            <div
                                v-if="item.instructions"
                                class="w-52 truncate text-2xs text-gray-700 dark:text-dark-175"
                            >
                                {{ __(item.instructions) }}
                            </div>
                        </div>
                        <ui-icon
                            name="arrow-right"
                            class="text-gray-600 group-hover:text-dark-800 dark:group-hover:text-dark-175"
                        />
                    </div>
                    <div
                        v-if="item.type === 'set'"
                        @click="addSet(item.handle)"
                        class="group flex items-center rounded-md px-2 py-1.5"
                    >
                        <ui-icon
                            :name="setIconName(item.icon)"
                            :directory="iconBaseDirectory"
                            class="h-9 w-9 rounded-sm border border-gray-600 bg-white p-2 text-gray-800 dark:border-dark-800 dark:bg-dark-650 dark:text-dark-175 ltr:mr-2 rtl:ml-2"
                        />
                        <div class="flex-1">
                            <div class="w-52 truncate text-sm font-medium text-gray-800 dark:text-dark-175">
                                {{ __(item.display || item.handle) }}
                            </div>
                            <div
                                v-if="item.instructions"
                                class="w-52 truncate text-2xs text-gray-700 dark:text-dark-175"
                            >
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
    },
};
</script>
