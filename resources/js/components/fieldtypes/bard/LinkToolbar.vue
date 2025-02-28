<template>

    <div class="bard-link-toolbar">
        <div>
            <div class="px-4 py-4 border-b dark:border-dark-900">

                <div class="flex">

                    <div class="h-8 mb-4 bg-gray-100 dark:bg-dark-600 text-gray-800 dark:text-dark-150 border dark:border-dark-200 rounded shadow-inner flex items-center rtl:ml-1 ltr:mr-1">
                        <select
                            class="input w-auto text-sm px-1"
                            v-model="linkType">
                            <option
                                v-for="visibleLinkType in visibleLinkTypes"
                                :value="visibleLinkType.type"
                            >
                                {{ visibleLinkType.title }}
                            </option>
                        </select>
                    </div>

                    <div class="h-8 mb-4 p-2 bg-gray-100 dark:bg-dark-600 text-gray-800 dark:text-dark-150 w-full border dark:border-dark-200 rounded shadow-inner placeholder:text-gray-600 dark:placeholder:dark-text-dark-175 flex items-center">

                        <!-- URL input -->
                        <input
                            v-if="linkType === 'url'"
                            v-model="url.url"
                            type="text"
                            ref="urlInput"
                            class="input h-auto text-sm"
                            :placeholder="__('URL')"
                            @keydown.enter.prevent="commit"
                        />

                        <!-- Email input -->
                        <input
                            v-else-if="linkType === 'mailto'"
                            v-model="urlData.mailto"
                            type="text"
                            ref="mailtoInput"
                            class="input h-auto text-sm"
                            :placeholder="__('Email Address')"
                            @keydown.enter.prevent="commit"
                        />

                        <!-- Phone input -->
                        <input
                            v-else-if="linkType === 'tel'"
                            v-model="urlData.tel"
                            type="text"
                            ref="telInput"
                            class="input h-auto text-sm"
                            placeholder="Phone Number"
                            @keydown.enter.prevent="commit"
                        />

                        <!-- Data input -->
                        <div
                            v-else
                            class="w-full flex items-center justify-between cursor-pointer"
                            @click="openSelector"
                        >

                            <loading-graphic v-if="isLoading" :inline="true" />

                            <div v-else class="flex-1 flex items-center rtl:ml-2 ltr:mr-2 truncate">
                                <img
                                    v-if="linkType === 'asset' && itemData.asset && itemData.isImage"
                                    :src="itemData.asset.thumbnail || itemData.asset.url"
                                    class="asset-thumbnail max-h-full max-w-full rounded w-6 h-6 rtl:ml-2 ltr:mr-2 object-cover lazyloaded"
                                >
                                {{ displayValue }}
                            </div>

                            <button
                            class="flex items-center"
                                v-tooltip="`${__('Browse')}...`"
                                :aria-label="`${__('Browse')}...`"
                                @click="openSelector"
                            >
                                <svg-icon v-show="linkType === 'asset'" name="folder-image" class="h-4 w-4" />
                                <svg-icon v-show="linkType !== 'asset'" name="folder-generic" class="h-4 w-4" />
                            </button>

                        </div>

                    </div>

                </div>


                <!-- Append string -->
                <div
                    v-if="linkType === 'entry'"
                    class="h-8 mb-4 p-2 bg-gray-100 dark:bg-dark-600 text-gray-800 dark:text-dark-150 w-full border dark:border-dark-200 rounded shadow-inner placeholder:text-gray-600 dark:placeholder:dark-text-dark-175 flex items-center"
                >
                    <input
                        type="text"
                        ref="input"
                        v-model="appends"
                        class="input h-auto text-sm placeholder-gray-50"
                        :placeholder="`${__('Append To URL')} (${__('Optional')})`"
                    />
                </div>

                <!-- Title attribute -->
                <div class="h-8 mb-4 p-2 bg-gray-100 dark:bg-dark-600 text-gray-800 dark:text-dark-150 w-full border dark:border-dark-200 rounded shadow-inner placeholder:text-gray-600 dark:placeholder:dark-text-dark-175 flex items-center" >
                    <input
                        type="text"
                        ref="input"
                        v-model="title"
                        class="input h-auto text-sm placeholder-gray-50"
                        :placeholder="`${__('Label')} (${__('Optional')})`"
                    />
                </div>

                <!-- Rel attribute -->
                <div class="h-8 p-2 bg-gray-100 dark:bg-dark-600 text-gray-800 dark:text-dark-150 w-full border dark:border-dark-200 rounded shadow-inner placeholder:text-gray-600 dark:placeholder:dark-text-dark-175 flex items-center" >
                    <input
                        type="text"
                        ref="input"
                        v-model="rel"
                        class="input h-auto text-sm placeholder-gray-50"
                        :placeholder="`${__('Relationship')} (${__('Optional')})`"
                    />
                </div>

                <label for="target-blank" class="mt-4 flex items-center font-normal cursor-pointer text-gray-800 dark:text-dark-150 hover:text-black dark:hover:text-dark-100" v-if="canHaveTarget">
                    <input class="checkbox rtl:ml-2 ltr:mr-2" type="checkbox" v-model="targetBlank" id="target-blank">
                    {{ __('Open in new window') }}
                </label>
            </div>

            <footer class="bg-gray-100 dark:bg-dark-575 rounded-b-md flex items-center justify-end space-x-3 rtl:space-x-reverse font-normal p-2">
                <button @click="$emit('canceled')" class="text-xs text-gray-600 dark:text-dark-175 hover:text-gray-800 dark:hover-text-dark-100">
                    {{ __('Cancel') }}
                </button>
                <button
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
                    {{ __('Save') }}
                </button>
            </footer>

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
                { type: 'mailto', title: __('Email') },
                { type: 'tel', title: __('Phone') },
            ],
            url: {},
            urlData: {},
            itemData: {},
            append: null,
            title: null,
            rel: null,
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
                case 'mailto':
                    return this.urlData.mailto ? this.urlData.mailto : null;
                case 'tel':
                    return this.urlData.tel ? this.urlData.tel : null;
            }
        },

        canCommit() {
            return !! this.url[this.linkType];
        },

        href() {
            return this.sanitizeLink(this.url[this.linkType]);
        },

        defaultRel() {
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
        },

        canHaveTarget() {
            return ['url', 'entry', 'asset'].includes(this.linkType);
        },

        selectedTextIsEmail() {
            const { view, state } = this.bard.editor
            const { from, to } = view.state.selection
            const text = state.doc.textBetween(from, to, '')

            return text.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/)
        },

    },

    watch: {

        linkType() {
            this.autofocus();
        },

        urlData: {
            deep: true,
            handler() {
                if (!['mailto', 'tel'].includes(this.linkType)) {
                    return;
                }
                this.setUrl(this.linkType, this.urlData[this.linkType]
                    ? `${this.linkType}:${this.urlData[this.linkType]}`
                    : null);
            },
        },

    },

    created() {
        this.applyAttrs(this.linkAttrs);

        this.bard.$on('link-selected', this.applyAttrs);
        this.bard.$on('link-deselected', () => this.$emit('deselected'));

        if (_.isEmpty(this.linkAttrs) && this.selectedTextIsEmail) {
            this.linkType = 'mailto'
            this.urlData = { mailto: this.selectedTextIsEmail }
        }
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
            this.urlData = { [this.linkType]: this.getUrlDataForUrl(attrs.href) };
            this.itemData = { [this.linkType]: this.getItemDataForUrl(attrs.href) };

            this.title = attrs.title;
            this.rel = attrs.href
                ? attrs.rel
                : this.defaultRel;
            this.targetBlank = attrs.href
                ? attrs.target === '_blank'
                : this.config.target_blank;
        },

        autofocus() {
            if (this.linkType === 'url') {
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.$refs.urlInput.focus();
                    }, 50);
                });
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
                href: `${this.href}${this.appends}`,
                rel: this.rel,
                target: (this.canHaveTarget && this.targetBlank) ? '_blank' : null,
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
            this.$axios.post(cp_url('assets-fieldtype'), {
                assets: [url],
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
            if (type) {
                return type;
            }

            const matches = url ? url.match(/^(mailto|tel):(.*)$/) : null;
            if (matches) {
                return matches[1];
            }

            return 'url';
        },

        getUrlDataForUrl(url) {
            const matches = url ? url.match(/^(mailto|tel):(.*)$/) : null;
            if (! matches) {
                return null;
            }

            return matches[2];
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
