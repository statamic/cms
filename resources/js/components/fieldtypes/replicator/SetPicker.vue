<template>

    <div class="replicator-set-picker">
        <dropdown-list class="align-left inline-block" placement="bottom-start" v-if="sets.length > 1" :scroll="true">
            <template v-slot:trigger>
                <button v-if="last" class="btn-round" v-tooltip.right="__('Add Set')" :aria-label="__('Add Set')">
                    <span class="icon icon-plus text-grey-80 antialiased"></span>
                </button>
                <button v-else class="dropdown-icon" v-tooltip.right="__('Add Set')" :aria-label="__('Add Set')">
                    <span class="icon icon-plus text-grey-50 antialiased" />
                </button>
            </template>

            <div v-for="set in sets" :key="set.handle">
                <dropdown-item :text="set.display || set.handle" @click="addSet(set.handle)" />
            </div>
        </dropdown-list>
        <button v-else :class="{'btn-round': last, 'dropdown-icon': !last }" v-tooltip.right="__('Add Set')" :aria-label="__('Add Set')" @click="addSet(sets[0].handle)">
            <span class="icon icon-plus text-grey-80 antialiased"></span>
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
