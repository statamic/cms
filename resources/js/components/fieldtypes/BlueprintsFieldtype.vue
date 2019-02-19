<template>

    <relationship-input
        :name="name"
        v-model="selections"
        :initial-data="initialData"
        :max-items="maxItems"
        :item-data-url="itemDataUrl"
        :selections-url="selectionsUrl"
        :columns="['title']"
    />

</template>

<script>
import RelationshipFieldtype from './relationship/RelationshipFieldtype.vue';

export default {

    mixins: [RelationshipFieldtype],

    computed: {

        itemDataUrl() {
            return cp_url(`fieldtypes/blueprints/data`);
        },

        baseSelectionsUrl() {
            return cp_url(`fieldtypes/blueprints`);
        },

    }

}
</script>
