<template>

    <div class="form-group">
        <label class="block font-medium mb-1">Add Pages</label>
        <relationship-input
            ref="input"
            name="entries"
            v-model="selections"
            :config="config"
            :site="site"
            :item-data-url="itemDataUrl"
            :selections-url="selectionsUrl"
            :search="true"
            :columns="columns"
            :can-create="true"
            :can-reorder="true"
            @item-data-updated="itemDataUpdated"
        />
    </div>

</template>

<script>
import qs from 'qs';

export default {

    props: {
        site: String,
        collections: Array,
    },

    data() {
        return {
            config: {
                type: 'relationship',
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
