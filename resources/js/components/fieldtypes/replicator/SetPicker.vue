<template>
    <template v-if="!hasMultipleSets">
        <Primitive @click="singleButtonClicked">
            <slot name="trigger" />
        </Primitive>
    </template>

    <ui-popover
        v-else
        inset
        :align="align"
        class="set-picker select-none w-72"
        :open="isOpen"
        @update:open="isOpen = $event"
        @clicked-away="$emit('clicked-away', $event)"
    >
        <template #trigger>
            <slot name="trigger" />
        </template>
        <template #default>
            <div class="flex items-center border-b border-gray-200 dark:border-gray-600 p-1.5">
                <ui-input
                    :placeholder="__('Search Sets')"
                    class="[&_svg]:size-5"
                    data-set-picker-search-input
                    icon-prepend="magnifying-glass"
                    ref="search"
                    size="sm"
                    type="text"
                    v-model="search"
                    v-show="showSearch"
                    variant="ghost"
                />
                <div v-if="showGroupBreadcrumb" class="flex items-center">
                    <ui-button @click="unselectGroup" size="xs" variant="ghost">
                        {{ __('Groups') }}
                    </ui-button>
                    <ui-icon name="chevron-right" class="size-3! mt-[1px]" />
                    <span class="text-gray-700 dark:text-gray-300 text-xs px-2">
                        {{ selectedGroupDisplayText }}
                    </span>
                </div>
            </div>
            <div class="max-h-[21rem] overflow-auto p-1.5">
                <div
                    v-for="(item, i) in items"
                    :key="item.handle"
                    class="cursor-pointer rounded-lg"
                    :class="{ 'bg-gray-100 dark:bg-gray-900': selectionIndex === i }"
                    @mouseover="selectionIndex = i"
                    :title="__(item.instructions)"
                >
                    <div v-if="item.type === 'group'" @click="selectGroup(item.handle)" class="group flex items-center rounded-lg p-2 gap-3">
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
                    <div v-if="item.type === 'set'" @click="addSet(item.handle)" class="group flex items-center rounded-xl p-2.5 gap-3">
                        <ui-icon :name="item.icon || 'plus'" :set="iconSet" class="size-4 text-gray-600 dark:text-gray-300" />
                        <ui-hover-card :delay="0">
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
                            <div class="max-w-96 max-h-[calc(80vh)] screen-fit" v-if="item.thumbnail">
                                <p v-if="item.instructions" class="text-gray-800 dark:text-gray-200 mb-2">
                                    {{ __(item.instructions) }}
                                </p>
                                <img :src="item.thumbnail" class="rounded-lg" />
                            </div>
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

        open() {
            this.isOpen = true;
        },
    },
};
</script>
