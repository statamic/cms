<template>

    <div class="bard-link-toolbar">

        <div>

            <!-- Link type select -->
            <div class="flex items-center px-2 py-1 border-b">

                <label
                    class="mr-1.5 flex items-center font-normal"
                    v-for="type in visibleLinkTypes"
                    :for="type.type"
                    :key="type.type"
                >
                    <input
                        class="mr-sm top-0"
                        type="radio"
                        name="link-type"
                        :id="type.type"
                        :checked="type.type === linkType"
                        @click="setLinkType(type.type)"
                    />
                    {{ type.title }}
                </label>

            </div>

            <div class="px-2 py-2 border-b">
                <div class="h-8 mb-2 p-1 border rounded border-grey-50 flex items-center">

                    <!-- URL input -->
                    <input
                        v-if="linkType === 'url'"
                        v-model="url.url"
                        type="text"
                        ref="urlInput"
                        class="input h-auto text-sm"
                        placeholder="URL"
                    />

                    <!-- Data input -->
                    <div
                        v-else
                        class="w-full flex justify-between"
                        @click="openSelector"
                    >

                        <loading-graphic v-if="isLoading" :inline="true" />

                        <div v-else class="flex-1 flex items-center mr-1 truncate">
                            <img
                                v-if="linkType === 'asset' && itemData.asset && itemData.isImage"
                                :src="itemData.asset.thumbnail || itemData.asset.url"
                                class="asset-thumbnail max-h-full max-w-full rounded w-6 h-6 mr-1 fit-cover lazyloaded"
                            >
                            {{ displayValue }}
                        </div>

                        <button
                            v-tooltip="`${__('Browse')}...`"
                            :aria-label="`${__('Browse')}...`"
                            @click="openSelector"
                        >
                            <svg-icon :name="linkType === 'asset' ? 'folder-image' : 'folder-generic'" class="h-4 w-4" />
                        </button>

                    </div>

                </div>

                <!-- Title attribute -->
                <div class="h-8 mb-2 p-1 border rounded border-grey-50 flex items-center" >
                    <input
                        type="text"
                        ref="input"
                        v-model="title"
                        class="input h-auto text-sm placeholder-gray-50"
                        :placeholder="`${__('Label')} (${__('Optional')})`"
                    />
                </div>

                <label for="target-blank" class="flex items-center font-normal">
                    <input class="checkbox mr-1" type="checkbox" v-model="targetBlank" id="target-blank">
                    {{ __('Open in new window') }}
                </label>
            </div>

            <div class="flex items-center justify-end space-x-1 font-normal px-2 py-1.5">
                <button
                    v-tooltip="__('Remove Link')"
                    :aria-label="__('Remove Link')"
                    @click="remove"
                    class="btn btn-sm"
                >
                    {{ __('Remove Link') }}
                </button>
                <button
                    :disabled="! canCommit"
                    v-tooltip="__('Apply Link')"
                    :aria-label="__('Apply Link')"
                    @click="commit"
                    class="btn btn-sm"
                >
                    {{ __('OK') }}
                </button>
            </div>

        </div>

        <!-- Selectors -->

        <relationship-input
            class="hidden"
            ref="relationshipInput"
            name="link"
            :value="[]"
            :config="relationshipConfig"
            :item-data-url="itemDataUrl"
            :selections-url="selectionsUrl"
            :filters-url="filtersUrl"
            :columns="[{ label: __('Title'), field: 'title' }]"
            :max-items="1"
            :site="bard.site"
            :search="true"
            @loading="isLoading = $event"
            @item-data-updated="entrySelected"
        />

         <stack
            v-if="showAssetSelector"
            name="asset-selector"
            @closed="closeAssetSelector"
        >
            <asset-selector
                :container="config.container"
                :folder="config.folder || '/'"
                :restrict-container-navigation="true"
                :restrict-folder-navigation="config.restrict_assets"
                :selected="[]"
                :view-mode="'grid'"
                :max-files="1"
                @selected="assetSelected"
                @closed="closeAssetSelector"
            />
        </stack>
    </div>

</template>

<script>
import qs from 'qs';
import AssetSelector from '../../assets/Selector.vue';
import SvgIcon from '../../SvgIcon.vue';

export default {

    components: {
        AssetSelector,
        SvgIcon
    },

    props: {
        bard: {},
        config: Object,
        linkAttrs: Object,
    },

    data() {
        return {
            linkType: 'url',
            linkTypes: [
                { type: 'url', title: __('URL') },
                { type: 'entry', title: __('Entry') },
                { type: 'asset', title: __('Asset') },
            ],
            url: {},
            itemData: {},
            title: null,
            targetBlank: null,
            showAssetSelector: false,
            isLoading: false,
        }
    },

    computed: {

        visibleLinkTypes() {
            return this.linkTypes.filter((type) => {
                if (type.type === 'asset' && ! this.config.container) {
                    return false;
                }
                return true;
            });
        },

        displayValue() {
            switch (this.linkType) {
                case 'url':
                    return this.url.url;
                case 'entry':
                    return this.itemData.entry ? this.itemData.entry.title : null;
                case 'asset':
                    return this.itemData.asset ? this.itemData.asset.basename : null;
            }
        },

        canCommit() {
            return !! this.url[this.linkType];
        },

        href() {
            return this.sanitizeLink(this.url[this.linkType]);
        },

        rel() {
            let rel = [];
            if (this.config.link_noopener) rel.push('noopener');
            if (this.config.link_noreferrer) rel.push('noreferrer');
            return rel.length ? rel.join(' ') : null;
        },

        relationshipConfig() {
            return {
                type: 'entries',
                collections: this.collections,
                max_items: 1,
            };
        },

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
            return utf8btoa(JSON.stringify(this.relationshipConfig));
        },

        collections() {
            return this.bard.meta.linkCollections;
        }

    },

    watch: {

        linkType() {
            this.autofocus();
        }

    },

    created() {
        this.applyAttrs(this.linkAttrs);

        this.bard.$on('link-selected', this.applyAttrs);
        this.bard.$on('link-deselected', () => this.$emit('deselected'));
    },

    mounted() {
        this.autofocus();
    },

    beforeDestroy() {
        this.bard.$off('link-selected');
        this.bard.$off('link-deselected');
    },

    methods: {

        applyAttrs(attrs) {
            this.linkType = this.getLinkTypeForUrl(attrs.href);

            this.url = { [this.linkType]: attrs.href };
            this.itemData = { [this.linkType]: this.getItemDataForUrl(attrs.href) };

            this.title = attrs.title;
            this.targetBlank = attrs.href
                ? attrs.target === '_blank'
                : this.config.target_blank;
        },

        setLinkType(type) {
            this.linkType = type;
        },

        autofocus() {
            if (this.linkType === 'url') {
                this.$nextTick(() => { this.$refs.urlInput.focus() });
            }
        },

        setUrl(type, url) {
            this.url = {
                ...this.url,
                [type]: url,
            }
        },

        setItemData(type, itemData) {
            this.itemData = {
                ...this.itemData,
                [type]: itemData,
            }
        },

        remove() {
            this.$emit('updated', { href: null });
        },

        commit() {
            if (!this.href) {
                return this.remove();
            }

            this.$emit('updated', {
                href: this.href,
                rel: this.rel,
                target: this.targetBlank ? '_blank' : null,
                title: this.title,
            });
        },

        sanitizeLink(link) {
            const str = link.trim();

            return str.match(/^\w[\w\-_\.]+\.(co|uk|com|org|net|gov|biz|info|us|eu|de|fr|it|es|pl|nz)/i)
                ? `https://${str}`
                : str
        },

        openSelector() {
            if (this.linkType === 'entry') {
                this.openEntrySelector();
            } else if (this.linkType === 'asset') {
                this.openAssetSelector();
            }
        },

        openEntrySelector() {
            this.$refs.relationshipInput.$refs.existing.click();
        },

        openAssetSelector() {
            this.showAssetSelector = true;
        },

        closeAssetSelector() {
            this.showAssetSelector = false;
        },

        assetSelected(data) {
            if (data.length) {
                this.loadAssetData(data[0]);
            }
        },

        loadAssetData(url) {
            this.$axios.get(cp_url('assets-fieldtype'), {
                params: { assets: [url] }
            }).then(response => {
                this.selectItem('asset', response.data[0])
                this.isLoading = false;
            });
        },

        entrySelected(data) {
            if (data.length) {
                this.selectItem('entry', data[0]);
            }
        },

        selectItem(type, item) {
            const ref = `${type}::${item.id}`;

            this.setItemData(type, item);
            this.setUrl(type, `statamic://${ref}`);

            this.putItemDataIntoMeta(ref, item);
        },

        putItemDataIntoMeta(ref, item) {
            let meta = this.bard.meta;
            meta.linkData[ref] = item;
            this.bard.updateMeta(meta);
        },

        getLinkTypeForUrl(url) {
            const { type } = this.parseDataUrl(url);
            return type || 'url';
        },

        getItemDataForUrl(url) {
            const { ref } = this.parseDataUrl(url);
            if (! ref) {
                return null;
            }

            return this.bard.meta.linkData[ref];
        },

        parseDataUrl(url) {
            if (! url) {
                return {}
            }

            const regex = /^statamic:\/\/((.*?)::(.*))$/;

            const matches = url.match(regex);
            if (! matches) {
                return {};
            }

            const [_, ref, type, id] = matches;

            return { ref, type, id};
        }
    }

}
</script>
