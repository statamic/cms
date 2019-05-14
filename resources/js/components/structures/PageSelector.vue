<template>

    <stack name="page-selector" narrow>
        <div class="bg-white h-full">

            <div class="bg-grey-20 px-3 py-1 border-b border-grey-30 text-lg font-medium flex items-center justify-between">
                {{ __('Add Page') }}
                <button
                    type="button"
                    class="btn-close"
                    @click="$emit('closed')"
                    v-html="'&times'" />
            </div>

            <div class="p-3">

                <!--
                <label class="block font-medium mb-1">Enter URL</label>
                <text-input />

                <div class="flex items-center my-3">
                    <div class="border-t border-grey-30 h-px flex-1" />
                    <div class="mx-1 text-xs text-grey italic">or</div>
                    <div class="border-t border-grey-30 h-px flex-1" />
                </div>
                -->

                <label class="block font-medium mb-1">Select existing pages</label>
                <relationship-input
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
                    @item-data-updated="itemData = $event"
                />

                <button
                    class="btn btn-primary w-full mt-4"
                    @click="$emit('selected', itemData)"
                    v-text="__('Add')" />

            </div>
        </div>

    </stack>

</template>

<script>
import qs from 'qs';

export default {

    props: {
        site: String,
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
            ],
            itemData: [],
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
                collections: ['pages'],
            });
        },

        configParameter() {
            return btoa(JSON.stringify(this.config));
        }

    }

}
</script>
