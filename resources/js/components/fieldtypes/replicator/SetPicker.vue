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

            <div :class="{'grid grid-flow-col w-80' : groupToShow != ''}" v-if="groupedSets.keys.length >= 1">
                <div>
                    <div v-for="group in groupedSets.keys">
                        <a @click="showGroup(group)" :class="{'' : groupToShow == group }">
                            <span :class="{ 'float-left' : groupToShow == '', 'float-left' : groupToShow != '' && groupToShow == group, 'float-left text-grey-60' : groupToShow != '' && groupToShow != group }">{{ group }}</span>
                            <span class="icon icon-arrow-right float-right"></span>
                            <span class="clear-both block"></span>
                        </a>
                    </div>
                </div>
                <div v-if="groupToShow != ''">
                    <div v-for="set in groupedSets.sets[groupToShow]" :key="set.handle">
                        <dropdown-item :text="set.display || set.handle" @click="addSet(set.handle)" />
                    </div>
                </div>
            </div>

            <div v-else>
                <div v-for="set in sets" :key="set.handle">
                    <dropdown-item :text="set.display || set.handle" @click="addSet(set.handle)" />
                </div>
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

    data() {
        return {
            groupToShow: '',
        }
    },

    computed: {

        groupedSets() {
            let groupedSets = this.groupBy(this.sets, 'group');
            return {
                keys: Object.keys(groupedSets),
                sets: groupedSets,
            };
        }

    },

    methods: {

        addSet(handle) {
            this.$emit('added', handle, this.index + 1);
            this.groupToShow = '';
        },

        groupBy(array, key) {
            const result = {};
            array.forEach(item => {
                if (!item[key]) {
                    item[key] = 'Default';
                }

                if (!result[item[key]]){
                    result[item[key]] = [];
                }

                result[item[key]].push(item);
            })
            return result;
        },

        showGroup(group) {
            return this.groupToShow = group;
        },

    }

}
</script>
