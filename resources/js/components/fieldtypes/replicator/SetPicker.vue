<template>

    <div class="replicator-set-picker">
        <dropdown-list class="align-left inline-block" placement="bottom-start" v-if="sets.length > 1" :scroll="true">
            <template v-slot:trigger>
                <button v-if="last" class="btn-round flex items-center justify-center" v-tooltip.right="__('Add Set')" :aria-label="__('Add Set')">
                    <svg-icon name="micro-plus" class="w-3 h-3 text-gray-800 group-hover:text-black" />
                </button>
                <button v-else class="dropdown-icon group" v-tooltip.right="__('Add Set')" :aria-label="__('Add Set')">
                    <svg-icon name="micro-plus" class="w-2.5 h-2.5 text-gray-600 group-hover:text-gray-900 transition duration-150" />
                </button>
            </template>

            <div v-for="set in sets" :key="set.handle">
                <dropdown-item :text="set.display || set.handle" @click="addSet(set.handle)" />
            </div>
        </dropdown-list>
        <button v-else :class="{'btn-round flex items-center justify-center': last, 'dropdown-icon': !last }" v-tooltip.right="__('Add Set')" :aria-label="__('Add Set')" @click="addSet(sets[0].handle)">
            <svg-icon name="micro-plus" class="w-3 h-3 text-gray-800 group-hover:text-black" />
        </button>
    </div>

</template>

<script>
export default {

    props: {
        sets: Array,
        index: Number,
        last: Boolean,
    },

    methods: {

        addSet(handle) {
            this.$emit('added', handle, this.index);
        }

    }

}
</script>
