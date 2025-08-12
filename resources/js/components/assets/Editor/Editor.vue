<template>
    <stack name="asset-editor" :before-close="shouldClose" :full="true" @closed="$emit('closed')" v-slot="{ close }">
        <div
            class="asset-editor relative flex h-full flex-col rounded-sm bg-gray-100 dark:bg-dark-800"
            :class="isImage ? 'is-image' : 'is-file'"
        >
            <div v-if="loading" class="loading">
                <Icon name="loading" />
            </div>

            <template v-if="!loading">
                <!-- Header -->
                <header id="asset-editor-header" class="relative flex w-full justify-between px-2">
                    <button
                        class="group flex items-center gap-3 p-4"
                        @click="open"
                        v-tooltip.right="__('Open in a new window')"
                        :aria-label="__('Open in a new window')"
                    >
                        <ui-icon name="folder-photos" class="size-5 group-hover:text-blue-600" />
                        <span class="text-sm group-hover:text-blue-600 dark:text-gray-400 dark:group-hover:text-gray-200">
                            {{ asset.path }}
                        </span>
                    </button>
                    <ui-button variant="ghost" icon="x" class="absolute top-1.5 end-1.5" round @click="confirmClose(close)" :aria-label="__('Close Editor')" />
                </header>

                <div class="flex flex-1 grow flex-col overflow-scroll md:flex-row md:justify-between">
                    <!-- Visual Area -->
                    <div class="editor-preview md:min-h-auto flex min-h-[45vh] w-full flex-1 flex-col justify-between bg-gray-800 shadow-[inset_0px_4px_3px_0px_black] dark:bg-gray-900 md:w-1/2 md:flex-auto md:grow lg:w-2/3 md:ltr:rounded-se-md">
                        <!-- Toolbar -->
                        <div v-if="isToolbarVisible" class="@container/toolbar dark flex items-center justify-center gap-2 px-2 py-4">
                            <ItemActions
                                :item="id"
                                :url="actionUrl"
                                :actions="actions"
                                @started="actionStarted"
                                @completed="actionCompleted"
                                v-slot="{ actions }"
                            >
                                <ui-button v-if="isImage && isFocalPointEditorEnabled" @click.prevent="openFocalPointEditor" icon="focus" variant="filled" v-tooltip="__('Focal Point')" />
                                <ui-button v-if="canRunAction('rename_asset')" @click.prevent="runAction(actions, 'rename_asset')" icon="rename" variant="filled" v-tooltip="__('Rename')" />
                                <ui-button v-if="canRunAction('move_asset')" @click.prevent="runAction(actions, 'move_asset')" icon="move-folder" variant="filled" v-tooltip="__('Move to Folder')" />
                                <ui-button v-if="canRunAction('replace_asset')" @click.prevent="runAction(actions, 'replace_asset')" icon="replace" variant="filled" v-tooltip="__('Replace')" />
                                <ui-button v-if="canRunAction('reupload_asset')" @click.prevent="runAction(actions, 'reupload_asset')" icon="upload-cloud" variant="filled" v-tooltip="__('Reupload')" />
                                <ui-button v-if="asset.allowDownloading" @click="download" icon="download" variant="filled" v-tooltip="__('Download')" />
                                <ui-button v-if="allowDeleting && canRunAction('delete')" @click="runAction(actions, 'delete')" icon="trash" variant="filled" v-tooltip="__('Delete')" />
                                <Dropdown class="me-4">
                                    <DropdownMenu>
                                        <DropdownItem
                                            v-for="action in filterForActionsMenu(actions)"
                                            :key="action.handle"
                                            :text="__(action.title)"
                                            :icon="action.icon"
                                            :variant="action.dangerous ? 'destructive' : 'default'"
                                            @click="action.run"
                                        />
                                    </DropdownMenu>
                                </Dropdown>
                            </ItemActions>
                        </div>

                        <!-- Image Preview -->
                        <div
                            v-if="asset.isImage || asset.isSvg || asset.isAudio || asset.isVideo"
                            class="editor-preview-image"
                        >
                            <div class="image-wrapper">
                                <!-- Image -->
                                <img v-if="asset.isImage" :src="asset.preview" class="asset-thumb" />

                                <!-- SVG -->
                                <div v-else-if="asset.isSvg" class="flex h-full w-full flex-col">
                                    <div class="grid grid-cols-3 gap-1">
                                        <div class="bg-checkerboard flex items-center justify-center p-3 aspect-square">
                                            <img :src="asset.url" class="asset-thumb relative z-10 size-4" />
                                        </div>
                                        <div class="bg-checkerboard flex items-center justify-center p-3 aspect-square">
                                            <img :src="asset.url" class="asset-thumb relative z-10 size-12" />
                                        </div>
                                        <div class="bg-checkerboard flex items-center justify-center p-3 aspect-square">
                                            <img :src="asset.url" class="asset-thumb relative z-10 size-24" />
                                        </div>
                                    </div>
                                    <div class="bg-checkerboard h-full min-h-0 mt-1 flex items-center justify-center p-3 aspect-square">
                                        <img :src="asset.url" class="asset-thumb relative z-10 max-h-full w-2/3 max-w-full" />
                                    </div>
                                </div>

                                <!-- Audio -->
                                <div class="w-full shadow-none" v-else-if="asset.isAudio">
                                    <audio :src="asset.url" class="w-full" controls preload="auto" />
                                </div>

                                <!-- Video -->
                                <video :src="asset.url" controls v-else-if="asset.isVideo" />
                            </div>
                        </div>

                        <div class="h-full" v-else-if="asset.isPdf">
                            <pdf-viewer :src="asset.pdfUrl" />
                        </div>

                        <div class="h-full" v-else-if="asset.isPreviewable && canUseGoogleDocsViewer">
                            <iframe
                                class="h-full w-full"
                                frameborder="0"
                                :src="'https://docs.google.com/gview?url=' + asset.permalink + '&embedded=true'"
                            ></iframe>
                        </div>
                    </div>

                    <!-- Fields Area -->
                    <PublishContainer
                        v-if="fields"
                        ref="container"
                        :name="publishContainer"
                        :reference="id"
                        :blueprint="fieldset"
                        :model-value="values"
                        :extra-values="extraValues"
                        :meta="meta"
                        :errors="errors"
                        @update:model-value="values = { ...$event, focus: values.focus }"
                    >
                        <div class="h-1/2 w-full overflow-scroll sm:p-4 md:h-full md:w-1/3 md:grow md:pt-px">
                            <div v-if="saving" class="loading">
                                <Icon name="loading" />
                            </div>

                            <PublishTabs />
                        </div>
                    </PublishContainer>
                </div>

                <div class="flex w-full items-center justify-end rounded-b border-t dark:border-gray-700 bg-gray-100 dark:bg-gray-900 px-4 py-3">
                    <div class="hidden h-full flex-1 gap-3 py-1 sm:flex">
                        <ui-badge v-if="isImage" icon="assets" :text="__('messages.width_x_height', { width: asset.width, height: asset.height })" />
                        <ui-badge icon="memory" :text="asset.size" />
                        <ui-badge icon="fingerprint" :text="asset.lastModifiedRelative" />
                    </div>
                    <div class="flex items-center space-x-3 rtl:space-x-reverse">
                        <ui-button icon="ui/chevron-left" @click="navigateToPreviousAsset" v-tooltip="__('Previous Asset')" />
                        <ui-button icon="ui/chevron-right" @click="navigateToNextAsset" v-tooltip="__('Next Asset')" />
                        <ui-button variant="primary" icon="save" @click="saveAndClose" v-if="!readOnly" :text="__('Save')" />
                    </div>
                </div>
            </template>

            <focal-point-editor
                v-if="showFocalPointEditor && isFocalPointEditorEnabled"
                :data="values.focus"
                :image="asset.preview"
                @selected="selectFocalPoint"
                @closed="closeFocalPointEditor"
            />

        <confirmation-modal
            v-if="closingWithChanges"
            :title="__('Unsaved Changes')"
            :body-text="__('Are you sure? Unsaved changes will be lost.')"
            :button-text="__('Discard Changes')"
            :danger="true"
            @confirm="confirmCloseWithChanges"
            @cancel="closingWithChanges = false"
        />
        </div>
    </stack>
</template>

<script>
import FocalPointEditor from './FocalPointEditor.vue';
import PdfViewer from './PdfViewer.vue';
import { pick, flatten } from 'lodash-es';
import { Dropdown, DropdownMenu, DropdownItem, PublishContainer, PublishTabs, Icon } from '@statamic/ui';
import ItemActions from '@statamic/components/actions/ItemActions.vue';

export default {
    emits: ['previous', 'next', 'saved', 'closed', 'action-started', 'action-completed'],

    components: {
        Dropdown,
        DropdownMenu,
        DropdownItem,
        ItemActions,
        FocalPointEditor,
        PdfViewer,
        PublishContainer,
        PublishTabs,
        Icon,
    },

    props: {
        id: {
            required: true,
        },
        readOnly: {
            type: Boolean,
        },
        showToolbar: {
            type: Boolean,
            default: true,
        },
        allowDeleting: {
            type: Boolean,
            default() {
                return true;
            },
        },
    },

    data() {
        return {
            loading: true,
            saving: false,
            asset: null,
            publishContainer: 'asset',
            values: {},
            extraValues: {},
            meta: {},
            fields: null,
            fieldset: null,
            showFocalPointEditor: false,
            error: null,
            errors: {},
            actions: [],
            closingWithChanges: false,
        };
    },

    computed: {
        isImage() {
            if (!this.asset) return false;

            return this.asset.isImage;
        },

        hasErrors: function () {
            return this.error || Object.keys(this.errors).length;
        },

        canUseGoogleDocsViewer() {
            return Statamic.$config.get('googleDocsViewer');
        },

        isFocalPointEditorEnabled() {
            return Statamic.$config.get('focalPointEditorEnabled');
        },

        isToolbarVisible() {
            return !this.readOnly && this.showToolbar;
        },
    },

    mounted() {
        this.load();

        window.addEventListener('keydown', this.keydown);
    },

    beforeUnmount() {
        window.removeEventListener('keydown', this.keydown);
    },

    events: {
        'close-child-editor': function () {
            this.closeFocalPointEditor();
            this.closeImageEditor();
            this.closeRenamer();
        },
    },

    methods: {
        /**
         * Load the asset data
         *
         * This component is given an asset ID.
         * It needs to get the corresponding data from the server.
         */
        load() {
            this.loading = true;

            const url = cp_url(`assets/${utf8btoa(this.id)}`);

            this.$axios.get(url).then((response) => {
                const data = response.data.data;
                this.asset = data;

                // If there are no fields, it will be an empty array when PHP encodes
                // it into JSON on the server. We'll ensure it's always an object.
                this.values = Array.isArray(data.values) ? {} : data.values;

                this.meta = data.meta;
                this.actionUrl = data.actionUrl;
                this.actions = data.actions;

                this.fieldset = data.blueprint;

                let fields = this.fieldset.tabs;
                fields = fields.map((tab) => tab.sections);
                fields = flatten(fields);
                fields = fields.map((section) => section.fields);
                fields = flatten(fields);
                this.fields = fields;

                this.extraValues = pick(this.asset, [
                    'filename',
                    'basename',
                    'extension',
                    'path',
                    'mimeType',
                    'width',
                    'height',
                    'duration',
                ]);

                this.loading = false;
            });
        },

        keydown(event) {
            if ((event.metaKey || event.ctrlKey) && event.key === 'ArrowLeft') {
                this.navigateToPreviousAsset();
            }

            if ((event.metaKey || event.ctrlKey) && event.key === 'ArrowRight') {
                this.navigateToNextAsset();
            }
        },

        navigateToPreviousAsset() {
            if (this.$dirty.has(this.publishContainer)) {
                this.save();
            }

            this.$emit('previous');
        },

        navigateToNextAsset() {
            if (this.$dirty.has(this.publishContainer)) {
                this.save();
            }

            this.$emit('next');
        },

        openFocalPointEditor() {
            this.showFocalPointEditor = true;
        },

        closeFocalPointEditor() {
            this.showFocalPointEditor = false;
        },

        selectFocalPoint(point) {
            point = point === '50-50-1' ? null : point;
            this.values['focus'] = point;
            this.$dirty.add(this.publishContainer);
        },

        save() {
            this.saving = true;
            const url = cp_url(`assets/${utf8btoa(this.id)}`);

            return this.$axios
                .patch(url, this.$refs.container.visibleValues)
                .then((response) => {
                    this.$emit('saved', response.data.asset);
                    this.$toast.success(__('Saved'));
                    this.saving = false;
                    this.clearErrors();
                })
                .catch((e) => {
                    this.saving = false;

                    if (e.response && e.response.status === 422) {
                        const { message, errors, error } = e.response.data;
                        this.error = message;
                        this.errors = errors;
                        this.$toast.error(error);
                    } else if (e.response) {
                        this.$toast.error(e.response.data.message);
                    } else {
                        this.$toast.error(__('Something went wrong'));
                    }

                    throw e;
                });
        },

        saveAndClose() {
            this.save().then(() => this.$emit('closed'));
        },

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        shouldClose() {
            if (this.$dirty.has(this.publishContainer)) {
                this.closingWithChanges = true;
                return false;
            }

            return true;
        },

        confirmClose(close) {
            if (this.shouldClose()) close();
        },

        confirmCloseWithChanges() {
            this.closingWithChanges = false;
            this.$emit('closed');
        },

        open() {
            window.open(this.asset.url, '_blank');
        },

        download() {
            window.open(this.asset.downloadUrl);
        },

        canRunAction(handle) {
            return this.actions.find((action) => action.handle == handle);
        },

        runAction(actions, handle) {
            actions
                .find((action) => action.handle === handle)
                .run();
        },

        actionStarted() {
            this.$emit('action-started');
        },

        actionCompleted(successful, response) {
            this.$emit('action-completed', successful, response);
            if (successful) {
                this.$emit('closed');
            }
        },

        filterForActionsMenu(actions) {
            // We filter out the actions that are already in the toolbar.
            // We don't want them to appear in the dropdown as well.
            // If we filtered them out in PHP they wouldn't appear as buttons.
            const buttonActions = [
                'rename_asset',
                'move_asset',
                'replace_asset',
                'reupload_asset',
                'download_asset',
                'delete',
                'copy_asset_url',
            ];

            return actions.filter((action) => !buttonActions.includes(action.handle));
        }
    },
};
</script>
