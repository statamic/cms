<template>
    <StackContent class="space-y-5">
        <section class="flex gap-3 items-center">
            <ui-select
                v-model="linkType"
                :options="linkTypes"
                option-label="title"
                option-value="type"
                class="w-1/4 min-w-24"
            />

            <div class="flex-1 min-w-0 link-fieldtype">
                <!-- URL input -->
                <ui-input
                    v-if="linkType === 'url'"
                    v-model="url.url"
                    type="text"
                    ref="urlInput"
                    autofocus
                    placeholder="https://"
                    @keydown.enter.prevent="commit"
                />

                <!-- Email input -->
                <ui-input
                    v-else-if="linkType === 'mailto'"
                    v-model="urlData.mailto"
                    type="text"
                    ref="mailtoInput"
                    :placeholder="__('Email Address')"
                    @keydown.enter.prevent="commit"
                />

                <!-- Phone input -->
                <ui-input
                    v-else-if="linkType === 'tel'"
                    v-model="urlData.tel"
                    ref="telInput"
                    :placeholder="__('Phone Number')"
                    @keydown.enter.prevent="commit"
                />

                <!-- Registered link type picker (entry, asset, or any custom type) -->
                <component
                    v-else-if="registeredLinkType"
                    :is="registeredLinkTypeComponent"
                    ref="typeField"
                    :config="registeredLinkType.config"
                    :meta="typeMeta"
                    :value="selectedTypeValue"
                    @update:value="typeSelected"
                    @update:meta="typeMetaUpdated"
                />
            </div>
        </section>

        <ui-separator :text="__('Advanced Options')" />

        <section class="space-y-5">
            <!-- Append attribute -->
            <ui-input
                v-if="linkType === 'entry'"
                type="text"
                v-model="appends"
                :prepend="__('Append')"
                :placeholder="__('?query=params#anchor')"
            />

            <!-- Title attribute -->
            <ui-input
                type="text"
                ref="input"
                v-model="title"
                :prepend="__('Label')"
                :placeholder="__('Add a link label')"
            />

            <!-- Rel attribute -->
            <ui-input
                type="text"
                ref="input"
                v-model="rel"
                :prepend="__('Rel')"
                :placeholder="__('noopener, noreferrer')"
            />

            <div class="flex items-center gap-2">
                <ui-switch
                    v-model="targetBlank"
                />
                <ui-description :text="__('Open in new window')" />
            </div>
        </section>

    </StackContent>


    <StackFooter>
        <template #end>
            <ui-button
                @click="$emit('canceled')"
                :text="__('Cancel')"
                variant="ghost"
            />
            <ui-button
                :text="__('Remove Link')"
                @click="remove"
            />
            <ui-button
                :text="__('Apply Link')"
                :disabled="!canCommit"
                @click="commit"
                variant="primary"
            />
        </template>
    </StackFooter>

    <!-- Selectors -->
</template>

<script>
import { Icon, StackContent, StackFooter } from '@/components/ui';

export default {
    emits: ['updated', 'canceled', 'deselected'],

    components: {
        Icon,
        StackContent,
        StackFooter,
    },

    provide: {
        isInLinkField: true,
    },

    props: {
        bard: {},
        config: Object,
        linkAttrs: Object,
    },

    data() {
        return {
            linkType: 'url',
            url: {},
            urlData: {},
            itemData: {},
            appends: null,
            title: null,
            rel: null,
            targetBlank: false,
            typeMetaOverrides: {},
        };
    },

    computed: {
        linkTypes() {
            return [
                { type: 'url', title: __('URL') },
                ...Object.entries(this.bard.meta.linkTypes ?? {}).map(([handle, type]) => ({
                    type: handle,
                    title: type.title,
                })),
                { type: 'mailto', title: __('Email') },
                { type: 'tel', title: __('Phone') },
            ];
        },

        registeredLinkType() {
            return this.bard.meta.linkTypes?.[this.linkType] ?? null;
        },

        registeredLinkTypeComponent() {
            return `${this.registeredLinkType.component}-fieldtype`;
        },

        typeMeta() {
            if (this.typeMetaOverrides[this.linkType]) {
                return this.typeMetaOverrides[this.linkType];
            }

            const meta = this.registeredLinkType?.meta ?? {};

            return this.selectedTypeData.length ? { ...meta, data: this.selectedTypeData } : meta;
        },

        canCommit() {
            return !!this.url[this.linkType];
        },

        href() {
            return this.sanitizeLink(this.url[this.linkType]);
        },

        normalizedAppends() {
            const value = this.appends;
            if (!value) return '';
            if (value.startsWith('?') || value.startsWith('#')) return value;
            return value.includes('=') ? `?${value}` : `#${value}`;
        },

        defaultRel() {
            let rel = [];
            if (this.config.link_noopener) rel.push('noopener');
            if (this.config.link_noreferrer) rel.push('noreferrer');
            return rel.length ? rel.join(' ') : null;
        },

        selectedTypeValue() {
            const { type, id } = this.parseDataUrl(this.url[this.linkType]);

            return type === this.linkType && id ? [id] : [];
        },

        selectedTypeData() {
            return this.itemData[this.linkType] ? [this.itemData[this.linkType]] : [];
        },

        canHaveTarget() {
            return !['mailto', 'tel'].includes(this.linkType);
        },

        selectedTextIsEmail() {
            const { view, state } = this.bard.editor;
            const { from, to } = view.state.selection;
            const text = state.doc.textBetween(from, to, '');

            return text.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/);
        },
    },

    watch: {
        linkType(type) {
            if (type != 'entry') {
                this.appends = null;
            }

            this.autofocus();

            if (this.registeredLinkType && !this.selectedTypeValue.length) {
                this.$nextTick(() => this.openSelector());
            }
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
            this.appends = this.getAppendsForUrl(attrs.href);
            this.url = { [this.linkType]: this.appends ? attrs.href?.replace(this.appends, '') : attrs.href };
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
                href: this.href + this.normalizedAppends,
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
            const field = this.$refs.typeField;

            if (!field) return;

            if (typeof field.linkExistingItem === 'function') field.linkExistingItem();
            else if (typeof field.openSelector === 'function') field.openSelector();
        },

        typeSelected(selected) {
            const id = selected[0] ?? null;

            this.setUrl(this.linkType, id ? `statamic://${this.linkType}::${id}` : null);
        },

        typeMetaUpdated(meta) {
            this.typeMetaOverrides = { ...this.typeMetaOverrides, [this.linkType]: meta };

            const item = meta.data?.[0];

            if (item) {
                this.setItemData(this.linkType, item);
                this.putItemDataIntoMeta(`${this.linkType}::${item.id}`, item);
            }
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

        getAppendsForUrl(urlString) {
            // appends is only relevant to entry links
            if (! urlString?.includes('statamic://entry::')) {
                return null;
            }

            return urlString.replace(urlString.split(/[?#]/)[0], '') || null;
        },

        parseDataUrl(url) {
            if (!url) {
                return {};
            }

            const appends = this.getAppendsForUrl(url);
            const regex = /^statamic:\/\/((.*?)::(.*))$/;

            const matches = (appends ? url.replace(appends, '') : url).match(regex);
            if (!matches) {
                return {};
            }

            const [_, ref, type, id] = matches;

            return { ref, type, id };
        },
    },
};
</script>
