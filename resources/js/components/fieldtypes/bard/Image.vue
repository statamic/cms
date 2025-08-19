<template>
    <node-view-wrapper>
        <div
            class="bard-inline-image-container shadow-sm"
            :class="{
                'border-blue-400': selected,
            }"
        >
            <div v-if="src" class="p-2 text-center" draggable="true" data-drag-handle>
                <div ref="content" hidden />
                <img :src="src" class="mx-auto block rounded-xs" />
            </div>

            <div
                class="flex items-center justify-center space-x-1 border-t px-2 py-2 text-center text-2xs text-white @container/toolbar dark:border-dark-900 dark:text-dark-150 sm:space-x-3 rtl:space-x-reverse"
            >
                <Button v-if="!src" size="sm" icon="folder-photos" :text="__('Choose Image')" @click="openSelector" />

                <Button v-if="src" size="sm" icon="edit" :text="__('Edit Image')" @click="edit" />
                <Button v-if="src" size="sm" icon="rename" :text="__('Override Alt')" :class="{ active: showingAltEdit }" @click="toggleAltEditor" />
                <Button v-if="src" size="sm" icon="replace" :text="__('Replace')" @click="openSelector" />
                <Button v-if="src" size="sm" icon="trash" :text="__('Remove')" @click="deleteNode" />
            </div>

            <div
                v-if="showingAltEdit"
                class="flex items-center rounded-b border-t p-2 dark:border-dark-900"
                @paste.stop
            >
                <Input
                    name="alt"
                    :focus="showingAltEdit"
                    v-model="alt"
                    :placeholder="assetAlt"
                    :prepend="__('Alt Text')"
                    class="flex-1"
                />
            </div>

            <stack v-if="showingSelector" name="asset-selector" @closed="closeSelector">
                <selector
                    :container="extension.options.bard.meta.assets.container"
                    :folder="extension.options.bard.config.folder || '/'"
                    :restrict-folder-navigation="extension.options.bard.config.restrict_assets"
                    :selected="selections"
                    :max-files="1"
                    :columns="extension.options.bard.meta.assets.columns"
                    @selected="assetsSelected"
                    @closed="closeSelector"
                >
                </selector>
            </stack>

            <asset-editor
                v-if="editing"
                :id="assetId"
                :showToolbar="false"
                :allow-deleting="false"
                @closed="closeEditor"
                @saved="editorAssetSaved"
                @actionCompleted="actionCompleted"
            >
            </asset-editor>
        </div>
    </node-view-wrapper>
</template>

<script>
import Asset from '../assets/Asset';
import { NodeViewWrapper } from '@tiptap/vue-3';
import Selector from '../../assets/Selector.vue';
import { containerContextKey } from '@/components/ui/Publish/Container.vue';
import { Input, Button } from '@/components/ui';

export default {
    mixins: [Asset],

    components: {
        NodeViewWrapper,
        Selector,
        Input,
        Button,
    },

    inject: {
        publishContainer: {
            from: containerContextKey,
        },
    },

    props: [
        'editor', // the editor instance
        'node', // access the current node
        'decorations', // an array of decorations
        'selected', // true when there is a NodeSelection at the current node view
        'extension', // access to the node extension, for example to get options
        'getPos', // get the document position of the current node
        'updateAttributes', // update attributes of the current node.
        'deleteNode', // delete the current node
    ],

    data() {
        return {
            assetId: null,
            assetAlt: null,
            editorAsset: null,
            showingSelector: false,
            loading: false,
            alt: this.node.attrs.alt,
            showingAltEdit: !!this.node.attrs.alt,
        };
    },

    computed: {
        src() {
            if (this.editorAsset) {
                return this.editorAsset.url;
            }
        },

        actualSrc() {
            if (this.editorAsset) {
                return `asset::${this.assetId}`;
            }

            return this.src;
        },

        selections() {
            return this.assetId ? [this.assetId] : [];
        },
    },

    created() {
        let src = this.node.attrs.src;

        if (this.node.isNew) {
            this.openSelector();
        }

        if (src && src.startsWith('asset:')) {
            this.assetId = src.substr(7);
        }

        let id = this.assetId || src;
        if (id) this.loadAsset(id);
    },

    watch: {
        actualSrc(src) {
            if (!this.node.attrs.src) {
                this.updateAttributes({ src, asset: !!this.assetId });
            }
        },

        alt(alt) {
            this.updateAttributes({ alt });
        },
    },

    methods: {
        openSelector() {
            this.showingSelector = true;
        },

        closeSelector() {
            this.showingSelector = false;
        },

        assetsSelected(selections) {
            this.loading = true;
            this.assetId = selections[0];
            this.loadAsset(this.assetId);
        },

        loadAsset(id) {
            this.$axios
                .post(cp_url('assets-fieldtype'), {
                    assets: [id],
                })
                .then((response) => {
                    this.setAsset(response.data[0]);
                });
        },

        setAsset(asset) {
            this.editorAsset = asset;
            this.assetId = asset.id;
            this.assetAlt = asset.values.alt;
            this.loading = false;
            this.updateAttributes({ src: this.actualSrc });
        },

        toggleAltEditor() {
            this.showingAltEdit = !this.showingAltEdit;
            if (!this.showingAltEdit) {
                this.alt = null;
            }
        },

        editorAssetSaved(asset) {
            this.setAsset(asset);
            this.closeEditor();
        },
    },

    updated() {
        // This is a workaround to avoid Firefox's inability to select inputs/textareas when the
        // parent element is set to draggable: https://bugzilla.mozilla.org/show_bug.cgi?id=739071
        this.$el.setAttribute('draggable', false);
    },
};
</script>
