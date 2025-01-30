<template>

    <stack name="asset-editor"
        :before-close="shouldClose"
        :full="true"
        @closed="close">

    <div class="asset-editor flex flex-col relative bg-gray-100 dark:bg-dark-800 h-full rounded" :class="isImage ? 'is-image' : 'is-file'">

        <div v-if="loading" class="loading">
            <loading-graphic />
        </div>

        <template v-if="!loading">

            <!-- Header -->
            <div id="asset-editor-header" class="flex justify-between w-full px-2 relative">
                <button class="flex items-center p-4 group" @click="open" v-tooltip.right="__('Open in a new window')" :aria-label="__('Open in a new window')">
                    <svg-icon name="folder-image" class="text-gray-700 dark:text-dark-175 h-5 w-5" />
                    <span class="rtl:mr-2 ltr:ml-2 text-sm text-gray-800 dark:text-dark-150 group-hover:text-blue dark:group-hover:text-dark-100">{{ asset.path }}</span>
                    <svg-icon name="micro/chevron-right" class="text-gray-700 dark:text-dark-175 h-5 w-5 group-hover:text-blue dark:group-hover:text-dark-100 rtl:rotate-180" />
                </button>
                <button class="btn-close absolute top-2 rtl:left-2.5 ltr:right-2.5" @click="close" :aria-label="__('Close Editor')">&times;</button>
            </div>

            <div class="flex flex-1 flex-col md:flex-row md:justify-between grow overflow-scroll">

                <!-- Visual Area -->
                <div class="editor-preview bg-gray-800 dark:bg-dark-950 rtl:md:rounded-tl-md ltr:md:rounded-tr-md flex flex-col justify-between flex-1 min-h-[45vh] md:min-h-auto md:flex-auto md:grow w-full md:w-1/2 lg:w-2/3 shadow-[inset_0px_4px_3px_0px_black]">

                    <!-- Toolbar -->
                    <div id="asset-editor-toolbar" class="@container/toolbar flex items-center justify-center py-4 px-2 text-2xs text-white dark:text-dark-100 text-center space-x-1 sm:space-x-3 rtl:space-x-reverse " v-if="isToolbarVisible">
                        <button v-if="isImage && isFocalPointEditorEnabled" type="button" class="flex bg-gray-750 dark:bg-dark-400 hover:bg-gray-900 dark:hover:bg-dark-600 hover:text-yellow-light dark:hover:text-yellow-dark rounded items-center justify-center px-3 py-1.5" @click.prevent="openFocalPointEditor">
                            <svg-icon name="focal-point" class="h-4" />
                            <span class="rtl:mr-2 ltr:ml-2 hidden @3xl/toolbar:inline-block">{{ __('Focal Point') }}</span>
                        </button>

                        <button v-if="canRunAction('rename_asset')" type="button" class="flex bg-gray-750 dark:bg-dark-400 hover:bg-gray-900 dark:hover:bg-dark-600 hover:text-yellow-light dark:hover:text-yellow-dark rounded items-center px-3 py-1.5" @click.prevent="runAction('rename_asset')">
                            <svg-icon name="rename-file" class="h-4" />
                            <span class="rtl:mr-2 ltr:ml-2 hidden @3xl/toolbar:inline-block">{{ __('Rename') }}</span>
                        </button>

                        <button v-if="canRunAction('move_asset')" type="button" class="flex bg-gray-750 dark:bg-dark-400 hover:bg-gray-900 dark:hover:bg-dark-600 hover:text-yellow-light dark:hover:text-yellow-dark rounded items-center px-3 py-1.5" @click.prevent="runAction('move_asset')">
                            <svg-icon name="move-file" class="h-4" />
                            <span class="rtl:mr-2 ltr:ml-2 hidden @3xl/toolbar:inline-block">{{ __('Move') }}</span>
                        </button>

                        <button v-if="canRunAction('replace_asset')" type="button" class="flex bg-gray-750 dark:bg-dark-400 hover:bg-gray-900 dark:hover:bg-dark-600 hover:text-yellow-light dark:hover:text-yellow-dark rounded items-center px-3 py-1.5" @click.prevent="runAction('replace_asset')">
                            <svg-icon name="swap" class="h-4" />
                            <span class="rtl:mr-2 ltr:ml-2 hidden @3xl/toolbar:inline-block">{{ __('Replace') }}</span>
                        </button>

                        <button v-if="canRunAction('reupload_asset')" type="button" class="flex bg-gray-750 dark:bg-dark-400 hover:bg-gray-900 dark:hover:bg-dark-600 hover:text-yellow-light dark:hover:text-yellow-dark rounded items-center px-3 py-1.5" @click.prevent="runAction('reupload_asset')">
                            <svg-icon name="upload-cloud" class="h-4" />
                            <span class="rtl:mr-2 ltr:ml-2 hidden @3xl/toolbar:inline-block">{{ __('Reupload') }}</span>
                        </button>

                        <button v-if="asset.allowDownloading" class="flex bg-gray-750 dark:bg-dark-400 hover:bg-gray-900 dark:hover:bg-dark-600 hover:text-yellow-light dark:hover:text-yellow-dark rounded items-center px-3 py-1.5" @click="download" :aria-label="__('Download file')">
                            <svg-icon name="download-desktop" class="h-4"/>
                            <span class="rtl:mr-2 ltr:ml-2 hidden @3xl/toolbar:inline-block">{{ __('Download') }}</span>
                        </button>

                        <button v-if="allowDeleting && canRunAction('delete')" @click="runAction('delete')" class="flex bg-gray-750 dark:bg-dark-400 hover:bg-gray-900 dark:hover:bg-dark-600 hover:text-red-400 dark:hover:text-dark-red rounded items-center text-center px-3 py-1.5">
                            <svg-icon name="trash" class="h-4" />
                            <span class="rtl:mr-2 ltr:ml-2 hidden @3xl/toolbar:inline-block">{{ __('Delete') }}</span>
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
                            <div v-else-if="asset.isSvg" class="bg-checkerboard h-full w-full flex flex-col">
                                <div class="flex border-b-2 border-gray-900">
                                    <div class="flex-1 order-r p-4 border-gray-900 flex items-center justify-center">
                                        <img :src="asset.url" class="asset-thumb w-4 h-4" />
                                    </div>
                                    <div class="flex-1 rtl:border-r ltr:border-l rtl:border-l ltr:border-r p-4 border-gray-900 flex items-center justify-center">
                                        <img :src="asset.url" class="asset-thumb w-12 h-12" />
                                    </div>
                                    <div class="flex-1 rtl:border-r ltr:border-l p-4 border-gray-900 flex items-center justify-center">
                                        <img :src="asset.url" class="asset-thumb w-24 h-24" />
                                    </div>
                                </div>
                                <div class="min-h-0 h-full p-4 flex items-center justify-center">
                                    <img :src="asset.url" class="asset-thumb w-2/3 max-w-full max-h-full" />
                                </div>
                            </div>

                            <!-- Audio -->
                            <div class="w-full shadow-none" v-else-if="asset.isAudio"><audio :src="asset.url" class="w-full" controls preload="auto"></audio></div>

                            <!-- Video -->
                            <div class="w-full shadow-none" v-else-if="asset.isVideo"><video :src="asset.url" class="w-full" controls></video></div>
                        </div>
                    </div>

                    <div class="h-full" v-else-if="asset.isPdf">
                        <pdf-viewer :src="asset.pdfUrl"></pdf-viewer>
                    </div>

                    <div class="h-full" v-else-if="asset.isPreviewable && canUseGoogleDocsViewer">
                        <iframe class="h-full w-full" frameborder="0" :src="'https://docs.google.com/gview?url=' + asset.permalink + '&embedded=true'"></iframe>
                    </div>
                </div>

                <!-- Fields Area -->
                <publish-container
                    v-if="fields"
                    :name="publishContainer"
                    :blueprint="fieldset"
                    :values="values"
                    :extra-values="extraValues"
                    :meta="meta"
                    :errors="errors"
                    @updated="values = { ...$event, focus: values.focus }"
                    v-slot="{ setFieldValue, setFieldMeta }"
                >
                    <div class="w-full sm:p-4 md:pt-px md:w-1/3 md:grow h-1/2 md:h-full overflow-scroll">

                        <div v-if="saving" class="loading">
                            <loading-graphic text="Saving" />
                        </div>

                        <div v-if="error" class="bg-red-500 text-white p-4 shadow mb-4" v-text="error" />

                        <publish-sections
                            :sections="fieldset.tabs[0].sections"
                            :read-only="readOnly"
                            @updated="setFieldValue"
                            @meta-updated="setFieldMeta"
                        />

                    </div>
                </publish-container>
            </div>

            <div class="bg-gray-200 dark:bg-dark-550 w-full border-t dark:border-dark-200 flex items-center justify-end py-3 px-4 rounded-b">
                <div id="asset-meta-data" class="flex-1 hidden sm:flex space-x-3 rtl:space-x-reverse py-1 h-full text-xs text-gray-800 dark:text-dark-150">
                    <div class="flex items-center bg-gray-400 dark:bg-dark-600 rounded py-1 rtl:pr-2 ltr:pl-2 rtl:pl-3 ltr:pr-3" v-if="isImage">
                        <svg-icon name="image-picture" class="h-3 rtl:ml-2 ltr:mr-2" />
                        <div class="">{{ __('messages.width_x_height', { width: asset.width, height: asset.height }) }}</div>
                    </div>
                    <div class="flex items-center bg-gray-400 dark:bg-dark-600 rounded py-1 rtl:pr-2 ltr:pl-2 rtl:pl-3 ltr:pr-3">
                        <svg-icon name="sd-card" class="h-3 rtl:ml-2 ltr:mr-2" />
                        <div class="">{{ asset.size }}</div>
                    </div>
                    <div class="flex items-center bg-gray-400 dark:bg-dark-600 rounded py-1 rtl:pr-2 ltr:pl-2 rtl:pl-3 ltr:pr-3">
                        <svg-icon name="thumbprint" class="h-3 rtl:ml-2 ltr:mr-2" />
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
            @completed="actionCompleted" />

        <focal-point-editor
            v-if="showFocalPointEditor && isFocalPointEditorEnabled"
            :data="values.focus"
            :image="asset.preview"
            @selected="selectFocalPoint"
            @closed="closeFocalPointEditor" />

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

    mixins: [
        HasHiddenFields,
    ],

    components: {
        EditorActions,
        FocalPointEditor,
        PdfViewer,
        PublishFields,
    },

    props: {
        id: {
            required: true
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
            }
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
        }
    },


    computed: {

        isImage() {
            if (! this.asset) return false;

            return this.asset.isImage;
        },

        hasErrors: function() {
            return this.error || Object.keys(this.errors).length;
        },

        canUseGoogleDocsViewer()
        {
            return Statamic.$config.get('googleDocsViewer');
        },

        isFocalPointEditorEnabled()
        {
            return Statamic.$config.get("focalPointEditorEnabled");
        },

        isToolbarVisible()
        {
            return ! this.readOnly && this.showToolbar;
        },

        actionsMenu()
        {
            // We filter out the actions that are already in the toolbar.
            // We don't want them to appear in the dropdown as well.
            // If we filtered them out in PHP they wouldn't appear as buttons.
            return this.actions.filter(action => ![
                'rename_asset',
                'move_asset',
                'replace_asset',
                'reupload_asset',
                'download_asset',
                'delete',
                'copy_asset_url',
            ].includes(action.handle));
        },

    },

    mounted() {
        this.load();
    },

    events: {
        'close-child-editor': function() {
            this.closeFocalPointEditor();
            this.closeImageEditor();
            this.closeRenamer();
        }
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

            this.$axios.get(url).then(response => {
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
                    .map(tab => tab.sections)
                    .flatten(true)
                    .map(section => section.fields)
                    .flatten(true)
                    .value();

                this.extraValues = pick(this.asset, ['filename', 'basename', 'extension', 'path', 'mimeType', 'width', 'height', 'duration']);

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
            point = (point === '50-50-1') ? null : point;
            this.values['focus'] = point;
            this.$dirty.add(this.publishContainer);
        },

        save() {
            this.saving = true;
            const url = cp_url(`assets/${utf8btoa(this.id)}`);

            this.$axios.patch(url, this.visibleValues).then(response => {
                this.$emit('saved', response.data.asset);
                this.$toast.success(__('Saved'));
                this.saving = false;
                this.clearErrors();
            }).catch(e => {
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
                if (! confirm(__('Are you sure? Unsaved changes will be lost.'))) {
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
            return _.find(this.actions, action => action.handle == handle);
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
    }

}
</script>
