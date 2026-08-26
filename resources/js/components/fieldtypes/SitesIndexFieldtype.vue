<template>
    <div class="flex flex-wrap gap-x-3 gap-y-1">
        <ItemLabel
            v-for="(item, index) in items"
            :key="index"
            :item="item"
            :group-label="groupLabel(item)"
        />
    </div>
</template>

<script>
import IndexFieldtype from './IndexFieldtype.vue';
import ItemLabel from '../inputs/relationship/ItemLabel.vue';
import {
    hasNamedSiteGroups,
    selectedSiteGroupLabel,
} from '@/util/site-groups.js';

export default {
    mixins: [IndexFieldtype],

    components: {
        ItemLabel,
    },

    computed: {
        items() {
            if (!this.value) {
                return [];
            }

            return Array.isArray(this.value) ? this.value : [this.value];
        },

        namedGroupsExist() {
            return hasNamedSiteGroups(Statamic.$config.get('sites') || []);
        },
    },

    methods: {
        groupLabel(item) {
            return selectedSiteGroupLabel(item, this.namedGroupsExist);
        },
    },
};
</script>
