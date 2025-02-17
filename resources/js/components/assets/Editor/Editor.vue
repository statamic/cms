<template>
    <stack name="asset-editor" :before-close="shouldClose" :full="true" @closed="close">
        <div
            class="asset-editor relative flex h-full flex-col rounded bg-gray-100 dark:bg-dark-800"
            :class="isImage ? 'is-image' : 'is-file'"
        >
            <div v-if="loading" class="loading">
                <loading-graphic />
            </div>

            <template v-if="!loading">
                <!-- Header -->
                <div id="asset-editor-header" class="relative flex w-full justify-between px-2">
                    <button
                        class="group flex items-center p-4"
                        @click="open"
                        v-tooltip.right="__('Open in a new window')"
                        :aria-label="__('Open in a new window')"
                    >
                        <svg-icon name="folder-image" class="h-5 w-5 text-gray-700 dark:text-dark-175" />
                        <span
                            class="text-sm text-gray-800 group-hover:text-blue dark:text-dark-150 dark:group-hover:text-dark-100 ltr:ml-2 rtl:mr-2"
                            >{{ asset.path }}</span
                        >
                        <svg-icon
                            name="micro/chevron-right"
                            class="h-5 w-5 text-gray-700 group-hover:text-blue dark:text-dark-175 dark:group-hover:text-dark-100 rtl:rotate-180"
                        />
                    </button>
                    <button
                        class="btn-close absolute top-2 ltr:right-2.5 rtl:left-2.5"
                        @click="close"
                        :aria-label="__('Close Editor')"
                    >
                        &times;
                    </button>
                </div>

                <div class="flex flex-1 grow flex-col overflow-scroll md:flex-row md:justify-between">
                    <!-- Visual Area -->
                    <div
                        class="editor-preview md:min-h-auto flex min-h-[45vh] w-full flex-1 flex-col justify-between bg-gray-800 shadow-[inset_0px_4px_3px_0px_black] dark:bg-dark-950 md:w-1/2 md:flex-auto md:grow lg:w-2/3 ltr:md:rounded-tr-md rtl:md:rounded-tl-md"
                    >
                        <!-- Toolbar -->
                        <div
                            id="asset-editor-toolbar"
                            class="flex items-center justify-center space-x-1 px-2 py-4 text-center text-2xs text-white @container/toolbar dark:text-dark-100 sm:space-x-3 rtl:space-x-reverse"
                            v-if="isToolbarVisible"
                        >
                            <button
                                v-if="isImage && isFocalPointEditorEnabled"
                                type="button"
                                class="flex items-center justify-center rounded bg-gray-750 px-3 py-1.5 hover:bg-gray-900 hover:text-yellow-light dark:bg-dark-400 dark:hover:bg-dark-600 dark:hover:text-yellow-dark"
                                @click.prevent="openFocalPointEditor"
                            >
                                <svg-icon name="focal-point" class="h-4" />
                                <span class="hidden @3xl/toolbar:inline-block ltr:ml-2 rtl:mr-2">{{
                                    __('Focal Point')
                                }}</span>
                            </button>

                            <button
                                v-if="canRunAction('rename_asset')"
                                type="button"
                                class="flex items-center rounded bg-gray-750 px-3 py-1.5 hover:bg-gray-900 hover:text-yellow-light dark:bg-dark-400 dark:hover:bg-dark-600 dark:hover:text-yellow-dark"
                                @click.prevent="runAction('rename_asset')"
                            >
                                <svg-icon name="rename-file" class="h-4" />
                                <span class="hidden @3xl/toolbar:inline-block ltr:ml-2 rtl:mr-2">{{
                                    __('Rename')
                                }}</span>
                            </button>

                            <button
                                v-if="canRunAction('move_asset')"
                                type="button"
                                class="flex items-center rounded bg-gray-750 px-3 py-1.5 hover:bg-gray-900 hover:text-yellow-light dark:bg-dark-400 dark:hover:bg-dark-600 dark:hover:text-yellow-dark"
                                @click.prevent="runAction('move_asset')"
                            >
                                <svg-icon name="move-file" class="h-4" />
                                <span class="hidden @3xl/toolbar:inline-block ltr:ml-2 rtl:mr-2">{{ __('Move') }}</span>
                            </button>

                            <button
                                v-if="canRunAction('replace_asset')"
                                type="button"
                                class="flex items-center rounded bg-gray-750 px-3 py-1.5 hover:bg-gray-900 hover:text-yellow-light dark:bg-dark-400 dark:hover:bg-dark-600 dark:hover:text-yellow-dark"
                                @click.prevent="runAction('replace_asset')"
                            >
                                <svg-icon name="swap" class="h-4" />
                                <span class="hidden @3xl/toolbar:inline-block ltr:ml-2 rtl:mr-2">{{
                                    __('Replace')
                                }}</span>
                            </button>

                            <button
                                v-if="canRunAction('reupload_asset')"
                                type="button"
                                class="flex items-center rounded bg-gray-750 px-3 py-1.5 hover:bg-gray-900 hover:text-yellow-light dark:bg-dark-400 dark:hover:bg-dark-600 dark:hover:text-yellow-dark"
                                @click.prevent="runAction('reupload_asset')"
                            >
                                <svg-icon name="upload-cloud" class="h-4" />
                                <span class="hidden @3xl/toolbar:inline-block ltr:ml-2 rtl:mr-2">{{
                                    __('Reupload')
                                }}</span>
                            </button>

                            <button
                                v-if="asset.allowDownloading"
                                class="flex items-center rounded bg-gray-750 px-3 py-1.5 hover:bg-gray-900 hover:text-yellow-light dark:bg-dark-400 dark:hover:bg-dark-600 dark:hover:text-yellow-dark"
                                @click="download"
                                :aria-label="__('Download file')"
                            >
                                <svg-icon name="download-desktop" class="h-4" />
                                <span class="hidden @3xl/toolbar:inline-block ltr:ml-2 rtl:mr-2">{{
                                    __('Download')
                                }}</span>
                            </button>

                            <button
                                v-if="allowDeleting && canRunAction('delete')"
                                @click="runAction('delete')"
                                class="flex items-center rounded bg-gray-750 px-3 py-1.5 text-center hover:bg-gray-900 hover:text-red-400 dark:bg-dark-400 dark:hover:bg-dark-600 dark:hover:text-dark-red"
                            >
                                <svg-icon name="trash" class="h-4" />
                                <span class="hidden @3xl/toolbar:inline-block ltr:ml-2 rtl:mr-2">{{
                                    __('Delete')
                                }}</span>
                            </button>

                            <dropdown-list class="mr-4" v-if="actionsMenu.length">
                                <data-list-inline-actions
                                    :item="id"
                                    :url="actionUrl"
                                    :actions="actionsMenu"
                                    @started="actionStarted"
                                    @completed="actionCompleted"
                                />
                            </dropdown-list>
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
                                <div v-else-if="asset.isSvg" class="bg-checkerboard flex h-full w-full flex-col">
                                    <div class="flex border-b-2 border-gray-900">
                                        <div
                                            class="order-r flex flex-1 items-center justify-center border-gray-900 p-4"
                                        >
                                            <img :src="asset.url" class="asset-thumb h-4 w-4" />
                                        </div>
                                        <div
                                            class="flex flex-1 items-center justify-center border-gray-900 p-4 ltr:border-l ltr:border-r rtl:border-l rtl:border-r"
                                        >
                                            <img :src="asset.url" class="asset-thumb h-12 w-12" />
                                        </div>
                                        <div
                                            class="flex flex-1 items-center justify-center border-gray-900 p-4 ltr:border-l rtl:border-r"
                                        >
                                            <img :src="asset.url" class="asset-thumb h-24 w-24" />
                                        </div>
                                    </div>
                                    <div class="flex h-full min-h-0 items-center justify-center p-4">
                                        <img :src="asset.url" class="asset-thumb max-h-full w-2/3 max-w-full" />
                                    </div>
                                </div>

                                <!-- Audio -->
                                <div class="w-full shadow-none" v-else-if="asset.isAudio">
                                    <audio :src="asset.url" class="w-full" controls preload="auto"></audio>
                                </div>

                                <!-- Video -->
                                <div class="w-full shadow-none" v-else-if="asset.isVideo">
                                    <video :src="asset.url" class="w-full" controls></video>
                                </div>
                            </div>
                        </div>

                        <div class="h-full" v-else-if="asset.isPdf">
                            <pdf-viewer :src="asset.pdfUrl"></pdf-viewer>
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
                    <publish-container
                        v-if="fields"
                        ref="container"
                        :name="publishContainer"
                        :blueprint="fieldset"
                        :values="values"
                        :extra-values="extraValues"
                        :meta="meta"
                        :errors="errors"
                        @updated="values = { ...$event, focus: values.focus }"
                        v-slot="{ setFieldValue, setFieldMeta }"
                    >
                        <div class="h-1/2 w-full overflow-scroll sm:p-4 md:h-full md:w-1/3 md:grow md:pt-px">
                            <div v-if="saving" class="loading">
                                <loading-graphic text="Saving" />
                            </div>

                            <div v-if="error" class="mb-4 bg-red-500 p-4 text-white shadow" v-text="error" />

                            <publish-sections
                                :sections="fieldset.tabs[0].sections"
                                :read-only="readOnly"
                                @updated="setFieldValue"
                                @meta-updated="setFieldMeta"
                            />
                        </div>
                    </publish-container>
                </div>

                <div
                    class="flex w-full items-center justify-end rounded-b border-t bg-gray-200 px-4 py-3 dark:border-dark-200 dark:bg-dark-550"
                >
                    <div
                        id="asset-meta-data"
                        class="hidden h-full flex-1 space-x-3 py-1 text-xs text-gray-800 dark:text-dark-150 sm:flex rtl:space-x-reverse"
                    >
                        <div
                            class="flex items-center rounded bg-gray-400 py-1 dark:bg-dark-600 ltr:pl-2 ltr:pr-3 rtl:pl-3 rtl:pr-2"
                            v-if="isImage"
                        >
                            <svg-icon name="image-picture" class="h-3 ltr:mr-2 rtl:ml-2" />
                            <div class="">
                                {{ __('messages.width_x_height', { width: asset.width, height: asset.height }) }}
                            </div>
                        </div>
                        <div
                            class="flex items-center rounded bg-gray-400 py-1 dark:bg-dark-600 ltr:pl-2 ltr:pr-3 rtl:pl-3 rtl:pr-2"
                        >
                            <svg-icon name="sd-card" class="h-3 ltr:mr-2 rtl:ml-2" />
                            <div class="">{{ asset.size }}</div>
                        </div>
                        <div
                            class="flex items-center rounded bg-gray-400 py-1 dark:bg-dark-600 ltr:pl-2 ltr:pr-3 rtl:pl-3 rtl:pr-2"
                        >
                            <svg-icon name="thumbprint" class="h-3 ltr:mr-2 rtl:ml-2" />
                            <div class="" :title="asset.lastModified">{{ asset.lastModifiedRelative }}</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 rtl:space-x-reverse">
                        <button type="button" class="btn" @click="close">
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" class="btn-primary" @click="save" v-if="!readOnly">
                            {{ __('Save') }}
                        </button>
                    </div>
                </div>
            </template>

            <editor-actions
                v-if="actions.length"
                :id="id"
                :actions="actions"
                :url="actionUrl"
                @started="actionStarted"
                @completed="actionCompleted"
            />

            <focal-point-editor
                v-if="showFocalPointEditor && isFocalPointEditorEnabled"
                :data="values.focus"
                :image="asset.preview"
                @selected="selectFocalPoint"
                @closed="closeFocalPointEditor"
            />
        </div>
    </stack>
</template>

<script>
import EditorActions from './EditorActions.vue';
import FocalPointEditor from './FocalPointEditor.vue';
import PdfViewer from './PdfViewer.vue';
import PublishFields from '../../publish/Fields.vue';
import HasHiddenFields from '../../publish/HasHiddenFields';
import pick from 'underscore/modules/pick';

export default {
    emits: ['saved', 'closed', 'action-completed'],

    mixins: [HasHiddenFields],

    components: {
        EditorActions,
        FocalPointEditor,
        PdfViewer,
        PublishFields,
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
        };
    },

    computed: {
        store() {
            return this.$refs.container.store;
        },

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

        actionsMenu() {
            // We filter out the actions that are already in the toolbar.
            // We don't want them to appear in the dropdown as well.
            // If we filtered them out in PHP they wouldn't appear as buttons.
            return this.actions.filter(
                (action) =>
                    ![
                        'rename_asset',
                        'move_asset',
                        'replace_asset',
                        'reupload_asset',
                        'download_asset',
                        'delete',
                        'copy_asset_url',
                    ].includes(action.handle),
            );
        },
    },

    mounted() {
        this.load();
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
                this.values = _.isArray(data.values) ? {} : data.values;

                this.meta = data.meta;
                this.actionUrl = data.actionUrl;
                this.actions = data.actions;

                this.fieldset = data.blueprint;
                this.fields = _.chain(this.fieldset.tabs)
                    .map((tab) => tab.sections)
                    .flatten(true)
                    .map((section) => section.fields)
                    .flatten(true)
                    .value();

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

            this.$axios
                .patch(url, this.visibleValues)
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
                });
        },

        clearErrors() {
            this.error = null;
            this.errors = {};
        },

        close() {
            this.$emit('closed');
        },

        shouldClose() {
            if (this.$dirty.has(this.publishContainer)) {
                if (!confirm(__('Are you sure? Unsaved changes will be lost.'))) {
                    return false;
                }
            }

            return true;
        },

        open() {
            window.open(this.asset.url, '_blank');
        },

        download() {
            window.open(this.asset.downloadUrl);
        },

        canRunAction(handle) {
            return _.find(this.actions, (action) => action.handle == handle);
        },

        runAction(handle) {
            this.$events.$emit('editor-action-selected', {
                action: handle,
                selection: this.id,
            });
        },

        actionStarted(event) {
            this.$events.$emit('editor-action-started');
        },

        actionCompleted(successful, response) {
            this.$events.$emit('editor-action-completed', successful, response);
            this.$emit('action-completed', successful, response);
            if (successful) {
                this.close();
            }
        },
    },
};
</script>
