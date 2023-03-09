<template>

    <popover class="set-picker" :scroll="true" :autoclose="false" :placement="placement">
        <template #trigger>
            <slot name="trigger" />
        </template>
        <template #default>
            <div class="set-picker-header p-3 border-b text-xs flex items-center">
                <input type="text" class="py-1 px-2 border rounded w-full" :placeholder="__('Search Sets')" v-show="showSearch" />
                <button v-show="!showSearch" class="text-gray-700 hover:text-gray-900">
                    <svg-icon name="chevron-left" class="w-2 h-2 mx-1" />
                    {{ __('Set Group Name')}}
                </button>
            </div>
            <div class="p-1">
                <div v-for="set in sets" :key="set.handle" class="rounded">
                    <dropdown-item @click="addSet(set.handle)" class="flex items-center group px-2 py-1.5 hover:bg-gray-200 rounded-md">
                        <div class="h-10 w-10 rounded bg-white border border-gray-600 mr-2 p-2.5">
                            <svg-icon name="folder-generic" class="text-gray-800" />
                        </div>
                        <div class="flex-1">
                            <div class="text-md font-medium text-gray-800 truncate w-52">{{ set.display || set.handle }}</div>
                            <div v-if="set.instructions" class="text-2xs text-gray-700 truncate w-52">{{ set.instructions }}</div>
                        </div>
                        <svg-icon name="chevron-right-thin" class="text-gray-600 group-hover:text-gray-800" />
                    </dropdown-item>
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
            showSearch: true,
        }
    },

    methods: {

        addSet(handle) {
            this.$emit('added', handle);
        }

    }

}
</script>
