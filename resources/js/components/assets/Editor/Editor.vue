<template>
    <Stack size="full" open inset ref="stack" :before-close="shouldClose" @update:open="$emit('closed')" :show-close-button="false">
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
                        class="group flex items-center gap-2 sm:gap-3 p-4"
                        @click="open"
                        v-tooltip.right="__('Open in a new window')"
                        :aria-label="__('Open in a new window')"
                    >
                        <ui-icon name="folder-photos" class="size-5 group-hover:text-ui-accent-text/80" />
                        <span class="text-sm group-hover:text-ui-accent-text/80 dark:text-gray-400 dark:group-hover:text-gray-200">
                            {{ asset.path }}
                        </span>
                    </button>
                    <ui-button variant="ghost" icon="x" class="absolute top-1.5 end-1.5" round @click="confirmClose()" :aria-label="__('Close Editor')" />
                </header>

                <div class="flex flex-1 grow flex-col overflow-auto md:flex-row md:justify-between">
                    <!-- Visual Area -->
                    <div class="editor-preview md:min-h-auto flex min-h-[45vh] w-full flex-1 flex-col justify-between bg-gray-800 shadow-[inset_0px_4px_3px_0px_black] dark:bg-gray-900 md:w-1/2 md:flex-auto md:grow lg:w-2/3 md:ltr:rounded-se-xl">
                        <!-- Toolbar -->
                        <div v-if="isToolbarVisible" class="@container/toolbar dark flex flex-wrap items-center justify-center gap-2 px-2 py-4">
                            <ItemActions
                                :item="id"
                                :url="actionUrl"
                                :actions="actions"
                                @started="actionStarted"
                                @completed="actionCompleted"
                                v-slot="{ actions }"
                            >
                                <ui-button inset size="sm" v-if="isImage && isFocalPointEditorEnabled" @click.prevent="openFocalPointEditor" icon="focus" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Focal Point')" />
                                <ui-button inset size="sm" v-if="isCroppable" @click.prevent="openCropEditor" icon="crop" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Crop')" />
                                <ui-button inset size="sm" v-if="isImage && asset && asset.can_be_transparent" @click="showCheckerboard = !showCheckerboard" icon="eye" variant="ghost" :class="[showCheckerboard ? '[&_svg]:!opacity-45' : '[&_svg]:!opacity-100']" :text="__('Transparency')" />
                                <ui-button inset size="sm" v-if="canRunAction('rename_asset')" @click.prevent="runAction(actions, 'rename_asset')" icon="rename" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Rename')" />
                                <ui-button inset size="sm" v-if="canRunAction('move_asset')" @click.prevent="runAction(actions, 'move_asset')" icon="move-folder" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Move to Folder')" />
                                <ui-button inset size="sm" v-if="canRunAction('replace_asset')" @click.prevent="runAction(actions, 'replace_asset')" icon="replace" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Replace')" />
                                <ui-button inset size="sm" v-if="canRunAction('reupload_asset')" @click.prevent="runAction(actions, 'reupload_asset')" icon="upload-cloud" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Reupload')" />
                                <ui-button inset size="sm" @click="download" icon="download" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Download')" />
                                <ui-button inset size="sm" v-if="allowDeleting && canRunAction('delete')" @click="runAction(actions, 'delete')" icon="trash" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Delete')" />

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

                        <!-- Asset Preview Area -->
                        <div
                            v-if="asset.isImage || asset.isSvg || asset.isAudio || asset.isVideo || asset.preview"
                            class="flex flex-1 flex-col justify-center items-center p-8 h-full min-h-0"
                        >
                            <!-- Image -->
                            <div v-if="asset.isImage" class="max-w-full max-h-full" :class="{ 'bg-checkerboard before:opacity-100': asset.can_be_transparent && showCheckerboard }">
                                <img :src="asset.preview" class="relative asset-thumb shadow-ui-xl max-w-full max-h-full object-contain" />
                            </div>

                            <!-- SVG -->
                            <div v-else-if="asset.isSvg" class="flex h-full w-full flex-col shadow-ui-xl">
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
                            <video :src="asset.url" class="max-w-full max-h-full object-contain" controls v-else-if="asset.isVideo" />

                            <!-- Other thumbnail -->
                            <img v-else-if="asset.preview" :src="asset.preview" class="asset-thumb shadow-ui-xl max-w-full max-h-full object-contain" />
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
                        :read-only="readOnly"
                        :name="publishContainer"
                        :reference="id"
                        :blueprint="fieldset"
                        :model-value="values"
                        :extra-values="extraValues"
                        :meta="meta"
                        :errors="errors"
                        @update:model-value="updateValues"
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
                    <div class="hidden h-full flex-1 gap-2 sm:gap-3 py-1 sm:flex">
                        <ui-badge pill v-if="asset.width && asset.height" icon="assets" :text="__('messages.width_x_height', { width: asset.width, height: asset.height })" />
                        <ui-badge pill icon="memory" :text="asset.size" />
                        <ui-badge pill icon="fingerprint">
                            <time
                                :datetime="asset.lastModified"
                                v-tooltip="$date.format(asset.lastModified)"
                                v-text="asset.lastModifiedRelative" />
                        </ui-badge>
                    </div>
                    <div class="flex items-center space-x-3 rtl:space-x-reverse">
                        <ui-button icon="chevron-left" @click="navigateToPreviousAsset" v-tooltip="__('Previous Asset')" />
                        <ui-button icon="chevron-right" @click="navigateToNextAsset" v-tooltip="__('Next Asset')" />
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

            <crop-editor
                v-if="isCroppable"
                :image="asset.preview"
                :mime-type="asset.mimeType"
                v-model:open="showCropEditor"
                @cropped="handleCropped"
            />

            <Modal
                :open="showCropConfirmation"
                :title="__('Save Cropped Image')"
                :dismissible="!uploadingCrop"
                @update:open="(open) => { if (!open) handleCropConfirmationDismissed(); }"
            >
                <div
                    v-if="uploadingCrop"
                    class="pointer-events-none absolute inset-0 flex select-none items-center justify-center bg-white bg-opacity-75 dark:bg-gray-850"
                >
                    <Icon name="loading" />
                </div>

                <p>{{ __('Would you like to save this as a new copy or replace the original image?') }}</p>

                <template #footer>
                    <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                        <Button
                            variant="ghost"
                            :disabled="uploadingCrop"
                            :text="__('Cancel')"
                            @click="handleCropConfirmationDismissed"
                        />
                        <Button
                            :disabled="uploadingCrop"
                            :text="__('Save as Copy')"
                            @click="uploadCroppedImage(false)"
                        />
                        <Button
                            variant="primary"
                            :disabled="uploadingCrop"
                            :text="__('Replace Original')"
                            @click="uploadCroppedImage(true)"
                        />
                    </div>
                </template>
            </Modal>

        <confirmation-modal
            v-model:open="closingWithChanges"
            :title="__('Unsaved Changes')"
            :body-text="__('Are you sure? Unsaved changes will be lost.')"
            :button-text="__('Discard Changes')"
            :danger="true"
            @confirm="confirmCloseWithChanges"
            @cancel="closingWithChanges = false"
        />
        </div>
    </Stack>
</template>

<script>
import FocalPointEditor from './FocalPointEditor.vue';
import CropEditor from './CropEditor.vue';
import PdfViewer from './PdfViewer.vue';
import { pick, flatten } from 'lodash-es';
import { router } from '@inertiajs/vue3';
import {
    Button,
    Dropdown,
    DropdownMenu,
    DropdownItem,
    Modal,
    PublishContainer,
    PublishTabs,
    Icon,
	Stack,
} from '@ui';
import ItemActions from '@/components/actions/ItemActions.vue';

export default {
    emits: ['previous', 'next', 'saved', 'closed', 'action-started', 'action-completed'],

    components: {
        Button,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        ItemActions,
        FocalPointEditor,
        CropEditor,
        Modal,
        PdfViewer,
        PublishContainer,
        PublishTabs,
        Icon,
	    Stack,
    },

    props: {
        id: {
            required: true,
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
            showCropEditor: false,
            showCheckerboard: true,
            error: null,
            errors: {},
            actions: [],
            closingWithChanges: false,
            croppedBlob: null,
            croppedMimeType: null,
            showCropConfirmation: false,
            uploadingCrop: false,
        };
    },

    computed: {
        readOnly() {
            return !this.asset.isEditable;
        },

        isImage() {
            if (!this.asset) return false;

            return this.asset.isImage;
        },

        isCroppable() {
            return this.isImage && this.asset.extension !== 'gif';
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
            this.closeCropEditor();
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

            return this.$axios.get(url).then((response) => {
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

        openCropEditor() {
            this.showCropEditor = true;
        },

        closeCropEditor() {
            this.showCropEditor = false;
        },

        handleCropped({ blob, mimeType }) {
            this.croppedBlob = blob;
            this.croppedMimeType = mimeType;
            // Close crop editor first, then show confirmation
            this.closeCropEditor();
            this.$nextTick(() => {
                this.showCropConfirmation = true;
            });
        },

        handleCropConfirmationDismissed() {
            if (this.uploadingCrop) return;

            this.showCropConfirmation = false;
            this.croppedBlob = null;
            this.croppedMimeType = null;
        },

        async uploadCroppedImage(replaceOriginal) {
            if (!this.croppedBlob || !this.asset) return;

            this.uploadingCrop = true;

            try {
                // Extract container and folder from asset ID (format: container::path)
                const [containerHandle, assetPath] = this.id.split('::');

                // Extract folder from path (dirname)
                const pathParts = assetPath.split('/');
                let filename = pathParts.pop();
                const folder = pathParts.length > 0 ? pathParts.join('/') : '/';

                // Update filename extension only when saving as new copy
                // When replacing original, keep original filename so server can find it to overwrite
                const shouldUpdateExtension = !replaceOriginal && this.croppedMimeType && (
                    this.croppedMimeType !== this.asset.mimeType
                );

                if (shouldUpdateExtension) {
                    const extensionMap = {
                        'image/jpeg': '.jpg',
                        'image/png': '.png',
                        'image/webp': '.webp',
                    };
                    const newExtension = extensionMap[this.croppedMimeType];
                    if (newExtension) {
                        // Remove old extension and add new one
                        const nameWithoutExt = filename.replace(/\.[^/.]+$/, '');
                        filename = nameWithoutExt + newExtension;
                    }
                }

                // Create FormData
                const formData = new FormData();
                // Use the File object directly - it already has the correct name and MIME type
                // If we need a different filename, create a new File with that name
                const fileToUpload = filename !== this.croppedBlob.name
                    ? new File([this.croppedBlob], filename, { type: this.croppedBlob.type })
                    : this.croppedBlob;
                formData.append('file', fileToUpload);
                formData.append('container', containerHandle);
                formData.append('folder', folder);
                formData.append('_token', Statamic.$config.get('csrfToken'));

                if (replaceOriginal) {
                    formData.append('option', 'overwrite');
                } else {
                    // Use timestamp option to avoid conflicts when saving as new copy
                    formData.append('option', 'timestamp');
                }

                const url = cp_url('assets');
                const response = await this.$axios.post(url, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                if (response.data && response.data.data) {
                    this.$toast.success(replaceOriginal ? __('Image replaced successfully') : __('Cropped image saved successfully'));

                    // If replacing, reload the current asset and bust browser cache; if new copy, redirect to the new asset
                    if (replaceOriginal) {
                        // Store original URLs for cache busting
                        const originalPreview = this.asset?.preview;
                        const originalThumbnail = this.asset?.thumbnail;

                        // Reload the asset and wait for it to complete
                        await this.load();

                        // After reload completes, force browser to reload images
                        if (this.asset) {
                            Statamic.$callbacks.call('bustAndReloadImageCaches', [originalPreview, originalThumbnail]);
                        }
                    } else {
                        // Extract container and path from the new asset ID (format: container::path)
                        const newAssetId = response.data.data.id;
                        const [containerHandle, assetPath] = newAssetId.split('::');

                        // Navigate to the edit URL for the new asset
                        const editUrl = cp_url(`assets/browse/${containerHandle}/${assetPath}/edit`);
                        router.get(editUrl);
                    }
                }

                this.croppedBlob = null;
                this.croppedMimeType = null;
                this.showCropConfirmation = false;
            } catch (error) {
                if (error.response && error.response.data) {
                    this.$toast.error(error.response.data.message || __('Failed to upload cropped image'));
                } else {
                    this.$toast.error(__('Failed to upload cropped image'));
                }
            } finally {
                this.uploadingCrop = false;
            }
        },

        updateValues(values) {
            let updated = { ...event, focus: values.focus };

            if (JSON.stringify(values) === JSON.stringify(updated)) {
                return
            }

            values = updated;
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
                    this.$nextTick(() => this.$refs.container.clearDirtyState());
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
            if (this.shouldClose()) this.$refs.stack.close();
        },

        confirmCloseWithChanges() {
            this.closingWithChanges = false;
            this.$refs.container.clearDirtyState();
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
