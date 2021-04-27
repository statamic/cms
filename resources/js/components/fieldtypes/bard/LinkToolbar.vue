<template>

    <div class="bard-link-toolbar">

        <div class="p-2">

            <!-- Link type select -->
            <div class="mb-2 flex items-center">

                <label
                    class="mr-1.5 flex items-center font-normal"
                    v-for="(title, type) in linkTypes"
                    :for="type"
                    :key="type"
                >
                    <input
                        class="mr-1"
                        type="radio"
                        name="link-type"
                        :id="type"
                        :checked="type === linkType"
                        @click="setLinkType(type)"
                    />
                    {{ title }}
                </label>

            </div>

            <!-- Link input -->
            <div class="h-8 mb-2 p-1 border rounded border-grey-50 flex items-center">

                <input
                    type="text"
                    ref="input"
                    class="input h-auto text-sm"
                    placeholder="URL"
                    :readonly="! isUrl"
                    :value="displayValue"
                    @keydown.enter.prevent="commit"
                />
                <button 
                    class="h-auto"
                    :class="{ hidden: isUrl }"
                    v-tooltip="`${__('Browse')}...`"
                    @click="openSelector">
                    <span class="icon icon-magnifying-glass" />
                </button>

            </div>

            <!-- Link attributes -->
            <div class="h-8 mb-2 p-1 border rounded border-grey-50 flex items-center">
                <input
                    type="text"
                    ref="input"
                    v-model="linkAttrs.title"
                    class="input h-auto text-sm"
                    :placeholder="__('Tooltip')"
                />
            </div>

            <div class="">
                <label class="flex items-center font-normal">
                    <input class="checkbox mr-1" type="checkbox" v-model="targetBlank">
                    {{ __('Open in new window') }}
                </label>
            </div>

        </div>

        <!-- Buttons -->
        <div class="p-2 border-t flex justify-between">
            <button class="h-auto" v-tooltip="__('Remove link')" @click="remove">
                <span class="mr-1 icon icon-trash" />
            </button>

            <button class="h-auto mr-1" v-tooltip="__('OK')"><!-- TODO @click -->
                <span class="icon icon-check" />
            </button>
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
            @item-data-updated="relationshipItemDataUpdated"
        />

         <stack
            v-if="showAssetSelector"
            name="asset-selector"
            @closed="closeAssetSelector"
        >
            <asset-selector
                :selected="[]"
                :container="'assets'"
                :max-files="1"
                @selected="assetSelected"
                @closed="closeAssetSelector"
            />
            <!-- 
                :container="container"
                :folder="folder"
                :restrict-container-navigation="true"
                :restrict-folder-navigation="restrictNavigation"
                :selected="selectedAssets"
                :view-mode="selectorViewMode"
                @selected="assetsSelected"
                @closed="closeSelector"

            -->
        </stack>
    </div>

</template>

<script>
import qs from 'qs';
import AssetSelector from '../../assets/Selector.vue';

export default {

    components: {
        AssetSelector
    },

    props: {
        bard: {},
        config: Object,
        initialLinkAttrs: Object,
    },

    data() {
        return {
            linkAttrs: this.initialLinkAttrs,
            // linkInput: this.initialLinkAttrs.href,
            // targetBlank: null,
            // isEditing: false,
            // internalLink: null,
            linkType: 'url',
            linkTypes: {
                url: __('URL'),
                entry: __('Entry'),
                asset: __('Asset'),
            },
            showAssetSelector: false,
            regularUrl: this.initialLinkAttrs.href,
            dataUrl: this.initialLinkAttrs.href,
            itemData: null,
            title: null,
            targetBlank: null,
        }
    },

    computed: {

        isUrl() {
            return this.linkType === 'url';
        },

        displayValue() {
            switch (this.linkType) {
                case 'url':
                    return this.regularUrl;
                case 'entry':
                    return this.itemData ? this.itemData.title : null;
                case 'asset':
                    return this.itemData ? this.itemData.basename : null;
            }
        },
        
        href() {
             if (this.isUrl) {
                return this.regularUrl
            }
           
            return this.itemData ? this.itemData.permalink : null;
        },

        // targetBlank() {
        //     // TODO check
        //     return this.href
        //         ? this.linkAttrs.target == '_blank'
        //         : this.config.target_blank;
        // },

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

    created() {
        console.log('LinkToolbar.created()')
        console.log(this.linkAttrs);

        // TODO test
        this.targetBlank = this.href
            ? this.linkAttrs.target == '_blank'
            : this.config.target_blank;

        // this.title = this.linkAttrs.title;
        
        this.itemData = this.getItemDataForUrl(this.linkAttrs.href);

        // this.bard.$on('link-selected', (selection) => {
        //     // This can't be a good way to do this.
        //     const attrs = selection.content().content.content[0].content.content[0].marks[0].attrs;
        //     this.linkAttrs = attrs;
        //     this.linkInput = attrs.href;
        //     this.targetBlank = attrs.target == '_blank';
        //     this.internalLink = this.getInternalLinkFromUrl(attrs.href);
        // });

        // this.bard.$on('link-deselected', () => this.$emit('deselected'));
    },

    beforeDestroy() {
        this.bard.$off('link-selected');
        this.bard.$off('link-deselected');
    },

    methods: {

        setLinkType(type) {
            this.linkType = type;

            this.openSelector();
        },

        remove() {
            this.$emit('updated', { href: null });
        },

        commit() {
            let rel = [];
            if (this.config.link_noopener) rel.push('noopener');
            if (this.config.link_noreferrer) rel.push('noreferrer');
            rel = rel.length ? rel.join(' ') : null;

            this.$emit('updated', {
                href: this.sanitizeLink(this.linkInput),
                rel,
                target: this.targetBlank ? '_blank' : null,
            });
        },

        // getLinkId(link) {
        //     const match = link.match(/^{{ link:(.*) }}$/);
        //     if (!match || !match[1]) return null;
        //     return match[1];
        // },

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
            this.showTypeSelector = false;

            this.$refs.relationshipInput.$refs.existing.click();
        },

        openAssetSelector() {
            this.showTypeSelector = false;

            this.showAssetSelector = true;
        },

        closeAssetSelector() {
            this.showAssetSelector = false;
        },

        assetSelected(data) {
            // console.log(data);
            if (data.length) {
                this.loadAssetData(data[0]);
            }
        },

        loadAssetData(url) {
            this.isLoading = true;

            this.$axios.get(cp_url('assets-fieldtype'), {
                params: { assets: [url] }
            }).then(response => {
                this.selectItem('asset', response.data[0])
                this.isLoading = false;
            });
        },

        relationshipItemDataUpdated(data) {
            if (data.length) {
                this.selectItem('entry', data[0]);
            }
        },

        selectItem(type, item) {
            console.log('LinkToolbar.selectItem()')
            console.log(type);
            console.log(item);
            const ref = `${type}::${item.id}`;

            this.putItemDataIntoMeta(ref, item);

            this.itemData = item;
            this.dataUrl = `statamic://${ref}`;

            this.commit();
        },

        putItemDataIntoMeta(ref, item) {
            let meta = this.bard.meta;
            meta.linkData[ref] = item;
            this.bard.updateMeta(meta);
        },

        getItemDataForUrl(url) {
            if (this.isDataUrl(url)) {
                return this.bard.meta.linkData[url.substr(11)];
            }
        },

        isDataUrl(url) {
            return url && url.startsWith('statamic://')
        }
    }

}
</script>
