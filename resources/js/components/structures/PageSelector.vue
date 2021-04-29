<template>

        <relationship-input
            class="hidden"
            ref="input"
            name="entries"
            :value="[]"
            :config="config"
            :site="site"
            :item-data-url="itemDataUrl"
            :selections-url="selectionsUrl"
            :filters-url="filtersUrl"
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
    },

    data() {
        return {
            config: {
                type: 'entries',
                collections: this.collections,
            },
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

        filtersUrl() {
            return cp_url('fieldtypes/relationship/filters') + '?' + qs.stringify({
                config: this.configParameter,
                collections: this.collections,
            });
        },

        configParameter() {
            return utf8btoa(JSON.stringify(this.config));
        }

    },

    methods: {

        linkExistingItem() {
            this.$refs.input.$refs.existing.click();
        },

        itemDataUpdated(data) {
            if (data.length) this.$emit('selected', data);
        }

    }

}
</script>
