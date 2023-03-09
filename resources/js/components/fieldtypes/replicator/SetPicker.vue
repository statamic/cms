<template>

    <popover ref="popover" class="set-picker" :scroll="true" :autoclose="false" :placement="placement">
        <template #trigger>
            <slot name="trigger" />
        </template>
        <template #default>
            <div class="set-picker-header p-3 border-b text-xs flex items-center">
                <input type="text" class="py-1 px-2 border rounded w-full" :placeholder="__('Search Sets')" v-show="showSearch" v-model="search" />
                <button v-show="showGroupBreadcrumb" @click="unselectGroup" class="text-gray-700 hover:text-gray-900">
                    <svg-icon name="chevron-left" class="w-2 h-2 mx-1" />
                    {{ selectedGroupDisplayText }}
                </button>
            </div>
            <div class="p-1">
                <div v-if="showGroups" v-for="group in sets" :key="group.handle" class="cursor-pointer rounded">
                    <div @click="selectGroup(group.handle)" class="flex items-center group px-2 py-1.5 hover:bg-gray-200 rounded-md">
                        <div class="h-10 w-10 rounded bg-white border border-gray-600 mr-2 p-2.5">
                            <svg-icon name="folder-generic" class="text-gray-800" />
                        </div>
                        <div class="flex-1">
                            <div class="text-md font-medium text-gray-800 truncate w-52">{{ group.display || group.handle }}</div>
                            <div v-if="group.instructions" class="text-2xs text-gray-700 truncate w-52">{{ group.instructions }}</div>
                        </div>
                        <svg-icon name="chevron-right-thin" class="text-gray-600 group-hover:text-gray-800" />
                    </div>
                </div>
                <div v-for="set in visibleSets" :key="set.handle" class="cursor-pointer rounded">
                    <div @click="addSet(set.handle)" class="flex items-center group px-2 py-1.5 hover:bg-gray-200 rounded-md">
                        <div class="flex-1">
                            <div class="text-md font-medium text-gray-800 truncate w-52">{{ set.display || set.handle }}</div>
                            <div v-if="set.instructions" class="text-2xs text-gray-700 truncate w-52">{{ set.instructions }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </popover>

</template>

<script>
export default {

    props: {
        sets: Array,
        placement: {
            type: String,
            default: 'bottom-start',
        }
    },

    data() {
        return {
            selectedGroupHandle: null,
            search: null
        }
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

        hasMultipleGroups() {
            return this.sets.length > 1;
        },

        selectedGroup() {
            return this.sets.find(group => group.handle === this.selectedGroupHandle);
        },

        selectedGroupDisplayText() {
            return this.selectedGroup ? this.selectedGroup.display || this.selectedGroup.handle : null;
        },

        visibleSets() {
            if (!this.selectedGroup && !this.search) return [];

            let sets = this.selectedGroup ? this.selectedGroup.sets : this.sets.reduce((sets, group) => {
                return sets.concat(group.sets);
            }, []);

            if (this.search) {
                return sets.filter(set => {
                    return set.display.toLowerCase().includes(this.search.toLowerCase())
                        || set.handle.toLowerCase().includes(this.search.toLowerCase());
                });
            }

            return sets;
        }

    },

    created() {
        if (this.sets.length === 1) {
            this.selectedGroupHandle = this.sets[0].handle;
        }
    },

    methods: {

        addSet(handle) {
            this.$emit('added', handle);
            this.unselectGroup();
            this.$refs.popover.close();
        },

        selectGroup(handle) {
            this.selectedGroupHandle = handle;
        },

        unselectGroup() {
            this.selectedGroupHandle = null;
        }

    }

}
</script>
