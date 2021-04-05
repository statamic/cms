<template>

    <div class="bard-link-toolbar">
        <div class="flex items-center px-2">
            <div class="flex-1 min-w-0">
                <div class="link-container">
                    <a
                        :href="actualLinkHref"
                        v-text="actualLinkText"
                        class="link"
                        target="_blank"
                        v-show="!isEditing"
                    ></a>
                </div>

                <div :class="isEditing ? 'flex items-center' : 'hidden'">
                    <input
                        type="text"
                        ref="input"
                        v-model="linkInput"
                        class="flex-1 input"
                        @keydown.enter.prevent="commit"
                    />
                </div>
            </div>
            <div class="bard-link-toolbar-buttons">
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
                    @item-data-updated="relationshipItemDataUpdated"
                />
                <button @click="edit" v-tooltip="__('Edit Link')" v-show="!isEditing">
                    <span class="icon icon-pencil" />
                </button>
                <button @click="openSelector" v-tooltip="`${__('Browse')}...`" v-show="isEditing">
                    <span class="icon icon-magnifying-glass" />
                </button>
                <button @click="remove" v-tooltip="__('Remove Link')" v-show="hasLink && isEditing">
                    <span class="icon icon-trash" />
                </button>
                <button @click="commit" v-tooltip="__('Done')" v-show="isEditing">
                    <span class="icon icon-check" />
                </button>
            </div>
        </div>
        <div class="p-sm pt-1 border-t border-faint-white" v-show="isEditing">
            <label class="text-2xs text-white flex items-center">
                <input class="checkbox mr-1 -mt-sm" type="checkbox" v-model="targetBlank">
                {{ __('Open in new window') }}
            </label>
        </div>
    </div>

</template>

<script>
import qs from 'qs';

export default {

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
        }
    },

    computed: {

        hasLink() {
            return this.actualLinkHref != null;
        },

        isInternalLink() {
            return false;
        },

        actualLinkHref() {
            return this.isInternalLink ? this.internalLink.url : this.linkAttrs.href;
        },

        actualLinkText() {
            return this.isInternalLink ? this.internalLink.text : this.linkAttrs.href;
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
            return this.bard.meta.link_collections;
        }

    },

    created() {
        this.targetBlank = this.linkAttrs.href
            ? this.linkAttrs.target == '_blank'
            : this.config.target_blank;

        if (!this.linkAttrs.href) {
            this.edit();
        }

        this.bard.$on('link-selected', (selection) => {
            // This can't be a good way to do this.
            const attrs = selection.content().content.content[0].content.content[0].marks[0].attrs;
            this.linkAttrs = attrs;
            this.linkInput = attrs.href;
            this.targetBlank = attrs.target == '_blank';
        });

        this.bard.$on('link-deselected', () => this.$emit('deselected'));
    },

    methods: {

        edit() {
            this.isEditing = true;
            this.$nextTick(() => this.$refs.input.focus());
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

        openSelector() {
            this.$refs.relationshipInput.$refs.existing.click();
        },

        relationshipItemDataUpdated(data) {
            if (! data.length) return;

            this.linkInput = 'statamic://entry::'+data[0].id;

            this.commit();
        }

    }

}
</script>
