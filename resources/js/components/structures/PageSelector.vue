<template>

        <relationship-input
            class="hidden"
            ref="input"
            name="entries"
            v-model="selections"
            :config="config"
            :site="site"
            :initial-data="[]"
            :item-data-url="itemDataUrl"
            :selections-url="selectionsUrl"
            :exclusions="exclusions"
            :search="true"
            :columns="columns"
            :can-create="false"
            :can-reorder="false"
            @item-data-updated="itemDataUpdated"
        />

</template>

<script>
import qs from 'qs';

export default {

    props: {
        site: String,
        collections: Array,
        exclusions: Array,
    },

    data() {
        return {
            config: {
                type: 'entries',
            },
            selections: [],
            columns: [
                { label: __('Title'), field: 'title' },
                { label: __('Slug'), field: 'slug' },
            ]
        }
    },

    computed: {

        itemDataUrl() {
            return cp_url('fieldtypes/relationship/data') + '?' + qs.stringify({
                config: this.configParameter
            });
        },

        selectionsUrl() {
            return cp_url('fieldtypes/relationship') + '?' + qs.stringify({
                config: this.configParameter,
                collections: this.collections,
            });
        },

        configParameter() {
            return btoa(JSON.stringify(this.config));
        }

    },

    methods: {

        linkExistingItem() {
            this.$refs.input.$refs.existing.click();
        },

        itemDataUpdated(itemData) {
            if (this.selections.length === 0) return;
            this.selections = [];
            this.$emit('selected', itemData);
        }

    }

}
</script>
