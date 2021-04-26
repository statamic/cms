<template>

    <div class="bard-link-toolbar">

        <div class="p-2 border-b">

            <!-- Link type select -->
            <div class="mb-2 flex items-center">

                <div class="mr-1.5 flex items-center" v-for="(title, type) in linkTypes" :key="type">
                    <div 
                        role="radio"
                        class="w-4 h-4 mr-1 cursor-default rounded-full border-grey-60"
                        :class="type === linkType ? 'border-4' : 'border'"
                        @click="setLinkType(type)"
                    />
                    {{ title }}
                </div>

            </div>

            <!-- Link input -->
            <div class="h-8 p-1 border rounded border-grey-50 flex items-center">

                <input
                    type="text"
                    ref="input"
                    v-model="linkInput"
                    class="input h-auto text-sm"
                    :readonly="isData"
                    @keydown.enter.prevent="commit"
                />
                <button 
                    class="h-auto"
                    :class="{ hidden: !isData }"
                    v-tooltip="`${__('Browse')}...`"
                    @click="openSelector">
                    <span class="icon icon-magnifying-glass" />
                </button>

            </div>

        </div>

        <!-- Link attributes -->
        <div class="p-2 border-b">

            <div class="mb-1 font-medium">Title</div>
            <div class="ml-1 h-8 mb-2 p-1 border rounded border-grey-50 flex items-center">

                <input
                    type="text"
                    ref="input"
                    v-model="linkInput"
                    class="input h-auto text-sm"
                />

            </div>

            <div class="p-sm">
                <label class="text-2xs flex items-center">
                    <input class="checkbox mr-1" type="checkbox" v-model="targetBlank">
                    {{ __('Open in new window') }}
                </label>
            </div>

        </div>

        <!-- Buttons -->
        <div class="p-2 flex justify-between">
            <button class="h-auto" v-tooltip="__('Remove link')">
                <span class="icon icon-trash" />
            </button>

            <button class="h-auto mr-1" v-tooltip="__('OK')">
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
                :view-mode="selectorViewMode"
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
            linkInput: this.initialLinkAttrs.href,
            targetBlank: null,
            isEditing: false,
            internalLink: null,
            showTypeSelector: false, // TODO rename
            showAssetSelector: false,
            linkType: 'url',
            linkTypes: {
                url: __('URL'),
                entry: __('Entry'),
                asset: __('Asset'),
            },
        }
    },

    computed: {

        isData() {
            return this.linkType !== 'url';
        },

        // hasLink() {
        //     return this.actualLinkHref != null;
        // },

        // isInternalLink() {
        //     return !! this.internalLink;
        // },

        // actualLinkHref() {
        //     return this.isInternalLink ? this.internalLink.permalink : this.linkAttrs.href;
        // },

        // actualLinkText() {
        //     return this.isInternalLink ? this.internalLink.title : this.linkAttrs.href;
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
        this.targetBlank = this.linkAttrs.href
            ? this.linkAttrs.target == '_blank'
            : this.config.target_blank;

        this.internalLink = this.getInternalLinkFromUrl(this.linkAttrs.href);

        if (!this.linkAttrs.href) {
            this.edit();
        }

        this.bard.$on('link-selected', (selection) => {
            // This can't be a good way to do this.
            const attrs = selection.content().content.content[0].content.content[0].marks[0].attrs;
            this.linkAttrs = attrs;
            this.linkInput = attrs.href;
            this.targetBlank = attrs.target == '_blank';
            this.internalLink = this.getInternalLinkFromUrl(attrs.href);
        });

        this.bard.$on('link-deselected', () => this.$emit('deselected'));
    },

    beforeDestroy() {
        this.bard.$off('link-selected');
        this.bard.$off('link-deselected');
    },

    methods: {

        setLinkType(type) {
            this.linkType = type;

            // this.openSelector();
        },

        edit() {
            this.isEditing = true;
            // this.$nextTick(() => this.$refs.input.focus());
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

        getLinkId(link) {
            const match = link.match(/^{{ link:(.*) }}$/);
            if (!match || !match[1]) return null;
            return match[1];
        },

        sanitizeLink(link) {
            const str = link.trim();

            return str.match(/^\w[\w\-_\.]+\.(co|uk|com|org|net|gov|biz|info|us|eu|de|fr|it|es|pl|nz)/i) ?
                        'https://' + str :
                            str;
        },

        toggleTypeSelector() {
            this.showTypeSelector = !this.showTypeSelector;
        },

        openTypeSelector() {
            this.showTypeSelector = true;
        },

        closeTypeSelector() {
            this.showTypeSelector = false;
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
            console.log(data);
            if (! data.length) return;

            const item = data[0];
            const ref = `asset::${item.id}`;

            this.pushItemDataIntoMeta(ref, item);

            this.linkInput = `statamic://${ref}`;

            this.commit();
        },

        relationshipItemDataUpdated(data) {
            if (! data.length) return;

            const item = data[0];
            const ref = `entry::${item.id}`;

            this.pushItemDataIntoMeta(ref, item);

            this.linkInput = `statamic://${ref}`;

            this.commit();
        },

        pushItemDataIntoMeta(ref, item) {
            let meta = this.bard.meta;
            meta.linkData[ref] = item;
            this.bard.updateMeta(meta);
        },

        getReferenceFromInternalUrl(url) {
            return url.substr(11); // everything after statamic://
        },

        getInternalLinkFromUrl(url) {
            if (!url || url.substr(0, 11) !== 'statamic://') return null;

            return this.bard.meta.linkData[this.getReferenceFromInternalUrl(url)];
        }

    }

}
</script>
