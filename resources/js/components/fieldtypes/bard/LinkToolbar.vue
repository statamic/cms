<template>
    <div class="bard-link-toolbar">
        <div>
            <div class="border-b bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800 rounded-b-xl rounded-t-md">
                <section class="flex gap-2 items-center p-4 border-b">
                    <ui-select
                        v-model="linkType"
                        :options="visibleLinkTypes"
                        option-label="title"
                        option-value="type"
                        size="sm"
                        class="flex-1"
                    />

                    <div class="flex-1">
                        <!-- URL input -->
                        <ui-input
                            v-if="linkType === 'url'"
                            v-model="url.url"
                            type="text"
                            ref="urlInput"
                            size="sm"
                            :placeholder="__('URL')"
                            @keydown.enter.prevent="commit"
                        />

                        <!-- Email input -->
                        <ui-input
                            v-else-if="linkType === 'mailto'"
                            v-model="urlData.mailto"
                            type="text"
                            ref="mailtoInput"
                            size="sm"
                            :placeholder="__('Email Address')"
                            @keydown.enter.prevent="commit"
                        />

                        <!-- Phone input -->
                        <ui-input
                            v-else-if="linkType === 'tel'"
                            v-model="urlData.tel"
                            ref="telInput"
                            size="sm"
                            :placeholder="__('Phone Number')"
                            @keydown.enter.prevent="commit"
                        />

                        <!-- Data input -->
                        <div
                            v-else
                            class="flex w-full min-w-[240px] cursor-pointer items-center justify-between"
                            @click="openSelector"
                        >
                            <Icon v-if="isLoading" name="loading" />

                            <div v-else class="flex flex-1 items-center truncate me-2">
                                <img
                                    v-if="linkType === 'asset' && itemData.asset && itemData.isImage"
                                    :src="itemData.asset.thumbnail || itemData.asset.url"
                                    class="asset-thumbnail lazyloaded h-6 max-h-full w-6 max-w-full rounded-sm object-cover me-2"
                                />
                                {{ displayValue }}
                            </div>

                            <button
                                class="flex items-center"
                                v-tooltip="`${__('Browse')}...`"
                                :aria-label="`${__('Browse')}...`"
                                @click="openSelector"
                            >
                                <ui-icon v-show="linkType === 'asset'" name="folder-image" class="size-4" />
                                <ui-icon v-show="linkType !== 'asset'" name="folder-generic" class="size-4" />
                            </button>
                        </div>
                    </div>
                </section>

                <div class="space-y-3 p-4">
                    <!-- Title attribute -->
                    <ui-input
                        type="text"
                        ref="input"
                        size="sm"
                        v-model="title"
                        :prepend="__('Label')"
                        :placeholder="__('Optional')"
                    />

                    <!-- Rel attribute -->
                    <ui-input
                        type="text"
                        ref="input"
                        size="sm"
                        v-model="rel"
                        :prepend="__('Rel')"
                        :placeholder="__('Optional')"
                    />

                    <ui-checkbox-item
                        :label="__('Open in new window')"
                        v-model="targetBlank"
                        size="sm"
                    />
                </div>

            </div>

            <footer class="flex items-center justify-end gap-3 rounded-b-md bg-gray-100 p-2 font-normal dark:bg-gray-800 rounded-b-xl">
                <ui-button
                    @click="$emit('canceled')"
                    :text="__('Cancel')"
                    size="sm"
                    inset
                    variant="ghost"
                />
                <ui-button
                    :text="__('Remove Link')"
                    @click="remove"
                    size="sm"
                    inset
                />
                <ui-button
                    :text="__('Apply Link')"
                    :disabled="!canCommit"
                    @click="commit"
                    size="sm"
                    variant="primary"
                />
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

        <stack v-if="showAssetSelector" name="asset-selector" @closed="closeAssetSelector">
            <asset-selector
                :container="config.container"
                :folder="config.folder || '/'"
                :restrict-folder-navigation="config.restrict_assets"
                :selected="[]"
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
import { Icon } from '@/components/ui';

export default {
    components: {
        AssetSelector,
        Icon,
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
            title: null,
            rel: null,
            targetBlank: false,
            showAssetSelector: false,
            isLoading: false,
        };
    },

    computed: {
        visibleLinkTypes() {
            return this.linkTypes.filter((type) => {
                if (type.type === 'asset' && !this.config.container) {
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
            return !!this.url[this.linkType];
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
                select_across_sites: this.config.select_across_sites,
            };
        },

        itemDataUrl() {
            return (
                cp_url('fieldtypes/relationship/data') +
                '?' +
                qs.stringify({
                    config: this.configParameter,
                })
            );
        },

        selectionsUrl() {
            return (
                cp_url('fieldtypes/relationship') +
                '?' +
                qs.stringify({
                    config: this.configParameter,
                    collections: this.collections,
                })
            );
        },

        filtersUrl() {
            return (
                cp_url('fieldtypes/relationship/filters') +
                '?' +
                qs.stringify({
                    config: this.configParameter,
                    collections: this.collections,
                })
            );
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
            const { view, state } = this.bard.editor;
            const { from, to } = view.state.selection;
            const text = state.doc.textBetween(from, to, '');

            return text.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/);
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
                this.setUrl(
                    this.linkType,
                    this.urlData[this.linkType] ? `${this.linkType}:${this.urlData[this.linkType]}` : null,
                );
            },
        },
    },

    created() {
        this.applyAttrs(this.linkAttrs);

        this.bard.events.on('link-selected', this.applyAttrs);
        this.bard.events.on('link-deselected', () => this.$emit('deselected'));

        if (Object.keys(this.linkAttrs).length === 0 && this.selectedTextIsEmail) {
            this.linkType = 'mailto';
            this.urlData = { mailto: this.selectedTextIsEmail };
        }
    },

    mounted() {
        this.autofocus();
    },

    beforeUnmount() {
        this.bard.events.off('link-selected');
        this.bard.events.off('link-deselected');
    },

    methods: {
        applyAttrs(attrs) {
            this.linkType = this.getLinkTypeForUrl(attrs.href);

            this.url = { [this.linkType]: attrs.href };
            this.urlData = { [this.linkType]: this.getUrlDataForUrl(attrs.href) };
            this.itemData = { [this.linkType]: this.getItemDataForUrl(attrs.href) };

            this.title = attrs.title;
            this.rel = attrs.href ? attrs.rel : this.defaultRel;
            this.targetBlank = attrs.href ? attrs.target === '_blank' : (this.config.target_blank || false);
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
            };
        },

        setItemData(type, itemData) {
            this.itemData = {
                ...this.itemData,
                [type]: itemData,
            };
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
                target: this.canHaveTarget && this.targetBlank ? '_blank' : null,
                title: this.title,
            });
        },

        sanitizeLink(link) {
            const str = link.trim();

            return str.match(/^\w[\w\-_\.]+\.(co|uk|com|org|net|gov|biz|info|us|eu|de|fr|it|es|pl|nz)/i)
                ? `https://${str}`
                : str;
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
            this.$axios
                .post(cp_url('assets-fieldtype'), {
                    assets: [url],
                })
                .then((response) => {
                    this.selectItem('asset', response.data[0]);
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
            if (!matches) {
                return null;
            }

            return matches[2];
        },

        getItemDataForUrl(url) {
            const { ref } = this.parseDataUrl(url);
            if (!ref) {
                return null;
            }

            return this.bard.meta.linkData[ref];
        },

        parseDataUrl(url) {
            if (!url) {
                return {};
            }

            const regex = /^statamic:\/\/((.*?)::(.*))$/;

            const matches = url.match(regex);
            if (!matches) {
                return {};
            }

            const [_, ref, type, id] = matches;

            return { ref, type, id };
        },
    },
};
</script>
