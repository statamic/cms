<template>
    <div class="flex items-center">

        <!-- Link type selector -->
        <div class="w-28 rtl:ml-4 ltr:mr-4">
            <v-select
                v-model="option"
                append-to-body
                :calculate-position="positionOptions"
                :options="options"
                :clearable="false"
                :reduce="(option) => option.value"

            >
                <template #option="{ label }">
                  {{ __(label) }}
                </template>
            </v-select>
        </div>

        <div class="flex-1">

            <!-- URL text input -->
            <text-input v-if="option === 'url'" v-model="urlValue" />

            <!-- Entry select -->
            <relationship-fieldtype
                v-if="option === 'entry'"
                ref="entries"
                handle="entry"
                :value="selectedEntries"
                :config="meta.entry.config"
                :meta="meta.entry.meta"
                @input="entriesSelected"
                @meta-updated="meta.entry.meta = $event"
            />

            <!-- Asset select -->
            <assets-fieldtype
                v-if="option === 'asset'"
                ref="assets"
                handle="asset"
                :value="selectedAssets"
                :config="meta.asset.config"
                :meta="meta.asset.meta"
                @input="assetsSelected"
                @meta-updated="meta.asset.meta = $event"
            />

        </div>
    </div>
</template>

<script>
import PositionsSelectOptions from '../../mixins/PositionsSelectOptions';

export default {

    mixins: [Fieldtype, PositionsSelectOptions],

    data() {

        return {
            option: this.meta.initialOption,
            options: this.initialOptions(),
            urlValue: this.meta.initialUrl,
            selectedEntries: this.meta.initialSelectedEntries,
            selectedAssets: this.meta.initialSelectedAssets,
            metaChanging: false,
        }

    },

    computed: {

        entryValue() {
            return this.selectedEntries.length
                ? `entry::${this.selectedEntries[0]}`
                : null
        },

        assetValue() {
            return this.selectedAssets.length
                ? `asset::${this.selectedAssets[0]}`
                : null
        },

        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

            switch (this.option) {
                case 'url':
                    return this.urlValue;
                case 'first-child':
                    return __('First child');
                case 'entry':
                    return data_get(this.meta, 'entry.meta.data.0.title', this.entryValue);
                case 'asset':
                    return data_get(this.meta, 'asset.meta.data.0.basename', this.assetValue);
            }

            return this.value;
        }

    },

    watch: {

        option(option, oldOption) {
            if (this.metaChanging) return;

            if (option === null) {
                this.update(null);
            } else if (option === 'url') {
                this.updateDebounced(this.urlValue);
            } else if (option === 'first-child') {
                this.update('@child');
            } else if (option === 'entry') {
                if (this.entryValue) {
                    this.update(this.entryValue);
                } else {
                    setTimeout(() => this.$refs.entries.linkExistingItem(), 0);
                }
            } else if (option === 'asset') {
                if (this.assetValue) {
                    this.update(this.assetValue);
                } else {
                    setTimeout(() => this.$refs.assets.openSelector(), 0);
                }
            }

            this.updateMeta({...this.meta, initialOption: option});
        },

        urlValue(url) {
            if (this.metaChanging) return;

            this.update(url);
            this.updateMeta({...this.meta, initialUrl: url});
        },

        meta(meta, oldMeta) {
            if (JSON.stringify(meta) === JSON.stringify(oldMeta)) return;

            this.metaChanging = true;
            this.urlValue = meta.initialUrl;
            this.option = meta.initialOption;
            this.selectedEntries = meta.initialSelectedEntries;
            this.selectedAssets = meta.initialSelectedAssets;
            this.$nextTick(() => this.metaChanging = false);
        }

    },

    methods: {

        initialOptions() {
            return [

                this.config.required
                    ? null
                    : { label: __('None'), value: null },

                { label: __('URL'), value: 'url' },

                this.meta.showFirstChildOption
                    ? { label: __('First Child'), value: 'first-child' }
                    : null,

                { label: __('Entry'), value: 'entry' },

                this.meta.showAssetOption
                    ? { label: __('Asset'), value: 'asset' }
                    : null,

            ].filter(option => option);
        },

        entriesSelected(entries) {
            this.selectedEntries = entries;
            this.update(this.entryValue);
            this.updateMeta({...this.meta, initialSelectedEntries: entries});
        },

        assetsSelected(assets) {
            this.selectedAssets = assets;
            this.update(this.assetValue);
            this.updateMeta({...this.meta, initialSelectedAssets: assets});
        }

    }

}
</script>
