<template>
    <Stack size="full" open inset ref="stack" :before-close="shouldClose" @update:open="$emit('closed')" :show-close-button="false">
        <div
            class="asset-editor relative flex h-full flex-col rounded-sm bg-gray-100 dark:bg-gray-850"
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
                        <div v-if="showToolbar" class="@container/toolbar dark flex flex-wrap items-center justify-center gap-2 px-2 py-4">
                            <ItemActions
                                :item="id"
                                :url="actionUrl"
                                :actions="actions"
                                @started="actionStarted"
                                @completed="actionCompleted"
                                v-slot="{ actions }"
                            >
                                <ui-button inset size="sm" v-if="asset.isEditable && isImage && isFocalPointEditorEnabled" @click.prevent="openFocalPointEditor" icon="focus" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Focal Point')" />
                                <ui-button inset size="sm" v-if="canCrop" @click.prevent="openCropEditor" icon="crop" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Crop')" />
                                <ui-button inset size="sm" v-if="asset.can_be_transparent" @click="showCheckerboard = !showCheckerboard" icon="eye" variant="ghost" :class="[showCheckerboard ? '[&_svg]:!opacity-45' : '[&_svg]:!opacity-100']" :text="__('Transparency')" />
                                <ui-button inset size="sm" v-if="canRunAction('rename_asset')" @click.prevent="runAction(actions, 'rename_asset')" icon="rename" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Rename')" />
                                <ui-button inset size="sm" v-if="canRunAction('move_asset')" @click.prevent="runAction(actions, 'move_asset')" icon="move-folder" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Move to Folder')" />
                                <ui-button inset size="sm" v-if="canRunAction('replace_asset')" @click.prevent="runAction(actions, 'replace_asset')" icon="replace" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Replace')" />
                                <ui-button inset size="sm" v-if="canRunAction('reupload_asset')" @click.prevent="runAction(actions, 'reupload_asset')" icon="upload-cloud" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Reupload')" />
                                <ui-button inset size="sm" @click="download" icon="download" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Download')" />
                                <ui-button inset size="sm" v-if="allowDeleting && canRunAction('delete')" @click="runAction(actions, 'delete')" icon="trash" variant="ghost" class="[&_svg]:!opacity-45" :text="__('Delete')" />

                                <Dropdown class="me-4" v-if="filterForActionsMenu(actions).length">
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
                            :class="imageTone && (asset.isImage || asset.isSvg) ? (imageTone === 'light' ? 'dark' : 'light') : ''"
                        >
                            <!-- Image: delay until tone analyzed to avoid checkerboard/background flicker -->
                            <template v-if="!(asset.isImage || asset.isSvg) || imageToneReady">
                                <!-- Image -->
                                <div v-if="asset.isImage" class="max-w-full max-h-full" :class="{ 'bg-checkerboard before:opacity-100 rounded-md': asset.can_be_transparent && showCheckerboard }">
                                    <img :src="asset.preview" class="relative asset-thumb shadow-ui-xl max-w-full max-h-full object-contain" />
                                </div>

                                <!-- SVG -->
                                <div v-else-if="asset.isSvg" class="flex h-full w-full flex-col shadow-ui-xl">
                                <div class="grid grid-cols-3 gap-1">
                                    <div class="flex items-center justify-center p-3 aspect-square" :class="{ 'bg-checkerboard before:opacity-100': showCheckerboard }">
                                        <img :src="asset.url" class="asset-thumb relative z-10 w-4" />
                                    </div>
                                    <div class="flex items-center justify-center p-3 aspect-square" :class="{ 'bg-checkerboard before:opacity-100': showCheckerboard }">
                                        <img :src="asset.url" class="asset-thumb relative z-10 w-12" />
                                    </div>
                                    <div class="flex items-center justify-center p-3 aspect-square" :class="{ 'bg-checkerboard before:opacity-100': showCheckerboard }">
                                        <img :src="asset.url" class="asset-thumb relative z-10 w-24" />
                                    </div>
                                </div>
                                <div class="h-full min-h-0 mt-1 flex items-center justify-center p-3 aspect-square" :class="{ 'bg-checkerboard before:opacity-100': showCheckerboard }">
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
                            </template>
                            <div v-else class="flex flex-1 items-center justify-center min-h-0">
                                <Icon name="loading" />
                            </div>
                        </div>

                        <pdf-viewer v-else-if="asset.isPdf" :src="asset.pdfUrl" />

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
                        <ui-button-group v-if="imageTone && (asset.isImage || asset.isSvg)" class="items-center">
                            <ui-button
                                size="sm"
                                :variant="toneOverride === null ? 'pressed' : 'default'"
                                :icon="autoToneIcon"
                                :text="__('Auto')"
                                :disabled="readOnly"
                                @click="setToneOverride(null)"
                            />
                            <ui-button
                                size="sm"
                                :variant="toneOverride === 'light' ? 'pressed' : 'default'"
                                icon="sun"
                                :text="__('Light')"
                                :disabled="readOnly"
                                @click="setToneOverride('light')"
                            />
                            <ui-button
                                size="sm"
                                :variant="toneOverride === 'dark' ? 'pressed' : 'default'"
                                icon="moon"
                                :text="__('Dark')"
                                :disabled="readOnly"
                                @click="setToneOverride('dark')"
                            />
                        </ui-button-group>
                        <ui-badge pill v-if="asset.width && asset.height" icon="assets" :text="__('messages.width_x_height', { width: Math.round(asset.width), height: Math.round(asset.height) })" />
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
                :asset="asset"
                :can-replace="asset.canReuploadCrop"
                v-model:open="showCropEditor"
                @replaced="handleCropReplaced"
                @created="handleCropCreated"
            />

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
    ButtonGroup,
    Dropdown,
    DropdownMenu,
    DropdownItem,
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
        ButtonGroup,
        Dropdown,
        DropdownMenu,
        DropdownItem,
        ItemActions,
        FocalPointEditor,
        CropEditor,
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
            imageTone: null, // effective tone for preview: override ?? calculated
            imageToneReady: false, // true once tone is set or not applicable (avoids checkerboard flicker)
            toneOverride: null, // null = auto, 'light' | 'dark' = user override (saved in yaml)
            calculatedTone: null, // detected tone (for Auto button icon/label)
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

        canCrop() {
            return this.isCroppable && this.asset.canCrop;
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

        // Icon for Auto button: show detected tone (sun/moon) when we have one, else wand
        autoToneIcon() {
            if (this.calculatedTone === 'light') return 'sun';
            if (this.calculatedTone === 'dark') return 'moon';
            return 'wand';
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

                // Tone: support auto (calculated) vs user override (saved in yaml as tone_override).
                this.toneOverride = data.tone_override ?? null;
                this.calculatedTone = data.tone_detected ?? data.tone ?? null;

                if (data.tone_detected || data.tone) {
                    this.imageTone = this.toneOverride ?? this.calculatedTone;
                    this.imageToneReady = true;
                } else if (data.isSvg) {
                    this.imageToneReady = false;
                    this.detectSvgTone(data.url)
                        .then((tone) => {
                            // When detection fails, default to 'dark' so black/dark SVGs get a light background.
                            this.calculatedTone = tone ?? 'dark';
                            this.imageTone = this.toneOverride ?? this.calculatedTone;
                        })
                        .finally(() => {
                            this.imageToneReady = true;
                        });
                } else {
                    this.imageTone = null;
                    this.imageToneReady = true;
                }

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

        setToneOverride(value) {
            if (this.readOnly) return;
            this.toneOverride = value; // null = auto, 'light' | 'dark' = override
            this.imageTone = this.toneOverride ?? this.calculatedTone;
            this.$dirty.add(this.publishContainer);
        },

        openCropEditor() {
            this.showCropEditor = true;
        },

        closeCropEditor() {
            this.showCropEditor = false;
        },

        async handleCropReplaced() {
            const originalPreview = this.asset?.preview;
            const originalThumbnail = this.asset?.thumbnail;
            await this.load();
            Statamic.$callbacks.call('bustAndReloadImageCaches', [originalPreview, originalThumbnail]);
        },

        handleCropCreated(newAssetId) {
            const [containerHandle, assetPath] = newAssetId.split('::');
            const editUrl = cp_url(`assets/browse/${containerHandle}/${assetPath}/edit`);
            router.get(editUrl);
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
            const payload = { ...this.$refs.container.visibleValues, tone_override: this.toneOverride };

            return this.$axios
                .patch(url, payload)
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
        },
        // Prefer parsing SVG XML so black/dark icons (e.g. fill="black" or no fill) are detected correctly.
        // Canvas-based detection can misread SVGs (e.g. white background or currentColor when drawn in Image()).
        detectSvgTone(url) {
            return this.detectSvgToneFromXml(url).then((tone) => {
                if (tone !== null) return tone;
                return this.detectImageTone(url);
            });
        },
        // Parse SVG fill/stroke from XML; matches server logic (DetectsTone::detectSvgToneFromSvgColors).
        detectSvgToneFromXml(url) {
            const luminanceThreshold = 0.4;
            const drawingElements = new Set(['path', 'circle', 'rect', 'ellipse', 'line', 'polyline', 'polygon', 'text', 'tspan', 'image']);

            return fetch(url, { credentials: 'same-origin' })
                .then((r) => (r.ok ? r.text() : Promise.reject()))
                .then((svgText) => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(svgText, 'image/svg+xml');
                    if (doc.querySelector('parsererror')) return null;

                    const luminances = [];
                    const all = doc.querySelectorAll('*');

                    all.forEach((el) => {
                        const style = (el.getAttribute('style') || '').trim();
                        const elementLuminances = [];
                        for (const attr of ['fill', 'stroke']) {
                            let value = (el.getAttribute(attr) || '').trim();
                            if (!value && style) {
                                const re = new RegExp(`\\b${attr}\\s*:\\s*([^;]+)`, 'i');
                                const m = style.match(re);
                                value = m ? m[1].trim() : '';
                            }
                            if (!value || value === 'none' || value === 'transparent') continue;
                            const lum = this.colorToLuminance(value);
                            if (lum !== null) elementLuminances.push(lum);
                        }
                        if (elementLuminances.length > 0) {
                            luminances.push(...elementLuminances);
                        } else {
                            const localName = (el.localName || el.nodeName || '').toLowerCase().split(':').pop();
                            if (drawingElements.has(localName)) luminances.push(0);
                        }
                    });

                    if (luminances.length === 0) return null;
                    const avg = luminances.reduce((a, b) => a + b, 0) / luminances.length;
                    return avg >= luminanceThreshold ? 'light' : 'dark';
                })
                .catch(() => null);
        },
        colorToLuminance(value) {
            value = (value || '').toLowerCase().trim();
            // currentColor is typically used for dark icons (inherit text color); treat as dark so background is light.
            if (value === 'currentcolor') return 0;

            let m = value.match(/^#([0-9a-f]{3})$/i);
            if (m) {
                const r = parseInt(m[1][0] + m[1][0], 16) / 255;
                const g = parseInt(m[1][1] + m[1][1], 16) / 255;
                const b = parseInt(m[1][2] + m[1][2], 16) / 255;
                return 0.299 * r + 0.587 * g + 0.114 * b;
            }
            m = value.match(/^#([0-9a-f]{6})$/i);
            if (m) {
                const r = parseInt(m[1].slice(0, 2), 16) / 255;
                const g = parseInt(m[1].slice(2, 4), 16) / 255;
                const b = parseInt(m[1].slice(4, 6), 16) / 255;
                return 0.299 * r + 0.587 * g + 0.114 * b;
            }
            m = value.match(/^rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/);
            if (m) {
                const r = Math.min(255, parseInt(m[1], 10)) / 255;
                const g = Math.min(255, parseInt(m[2], 10)) / 255;
                const b = Math.min(255, parseInt(m[3], 10)) / 255;
                return 0.299 * r + 0.587 * g + 0.114 * b;
            }
            m = value.match(/^rgba\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,/);
            if (m) {
                const r = Math.min(255, parseInt(m[1], 10)) / 255;
                const g = Math.min(255, parseInt(m[2], 10)) / 255;
                const b = Math.min(255, parseInt(m[3], 10)) / 255;
                return 0.299 * r + 0.587 * g + 0.114 * b;
            }
            const named = { black: 0, white: 1, red: 0.212, lime: 0.715, blue: 0.072, gray: 0.5, grey: 0.5, silver: 0.753, maroon: 0.144, green: 0.357, navy: 0.028, yellow: 0.927, olive: 0.502, purple: 0.132, teal: 0.357, fuchsia: 0.284, aqua: 0.787, orange: 0.695 };
            return named[value] !== undefined ? named[value] : null;
        },
        // Canvas-based tone detection; used as fallback for SVGs when XML parsing fails.
        // Same luminance formula and threshold (0.4) as server for consistent result.
        detectImageTone(src) {
            const maxSize = 64;
            const luminanceThreshold = 0.4;

            return new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';

                img.onerror = () => resolve(null);
                img.onload = () => {
                    try {
                        const w = img.naturalWidth;
                        const h = img.naturalHeight;
                        const scale = Math.min(1, maxSize / Math.max(w, h));
                        const cw = Math.max(1, Math.round(w * scale));
                        const ch = Math.max(1, Math.round(h * scale));

                        const canvas = document.createElement('canvas');
                        canvas.width = cw;
                        canvas.height = ch;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, cw, ch);
                        const data = ctx.getImageData(0, 0, cw, ch).data;

                        let sum = 0;
                        let count = 0;
                        for (let i = 0; i < data.length; i += 4) {
                            const a = data[i + 3] / 255;
                            if (a < 0.1) continue;
                            const r = data[i] / 255;
                            const g = data[i + 1] / 255;
                            const b = data[i + 2] / 255;
                            const l = 0.299 * r + 0.587 * g + 0.114 * b;
                            sum += l * a;
                            count += a;
                        }
                        const avgLuminance = count > 0 ? sum / count : 0.5;
                        resolve(avgLuminance >= luminanceThreshold ? 'light' : 'dark');
                    } catch {
                        resolve(null);
                    }
                };

                img.src = src;
            });
        },
    },
};
</script>
