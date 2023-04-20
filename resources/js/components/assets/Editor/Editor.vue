<template>

    <stack name="asset-editor"
        :before-close="shouldClose"
        :full="true"
        @closed="close">

    <div class="asset-editor" :class="isImage ? 'is-image' : 'is-file'">

        <div v-if="loading" class="loading">
            <loading-graphic />
        </div>

        <template v-if="!loading">

            <div class="editor-meta">
                <div class="asset-editor-meta-items">
                    <div class="meta-item">
                        <span class="meta-label">{{ __('Path') }}</span>
                        <span class="meta-value">{{ asset.path }}</span>
                    </div>
                    <div class="meta-item" v-if="isImage">
                        <span class="meta-label">{{ __('Dimensions') }}</span>
                        <span class="meta-value">{{ asset.width }} x {{ asset.height }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{ __('Size') }}</span>
                        <span class="meta-value">{{ asset.size }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{ __('Last Modified') }}</span>
                        <span class="meta-value" :title="asset.lastModified">{{ asset.lastModifiedRelative }}</span>
                    </div>
                </div>

                <div class="asset-editor-meta-actions">
                    <button @click="open" v-tooltip="__('Open in a new window')" :aria-label="__('Open in a new window')">
                        <svg-icon name="external-link" class="h-6 w-6"/>
                    </button>
                    <button @click="download" v-tooltip="__('Download file')" :aria-label="__('Download file')" v-if="asset.allowDownloading">
                        <svg-icon name="download" class="h-6 w-6"/>
                    </button>
                    <button @click="close" v-tooltip="__('Close editor')" :aria-label="__('Close editor')">
                        <svg-icon name="close" class="h-6 w-6"/>
                    </button>
                </div>
            </div>

            <div class="editor-main">

                <div class="editor-preview">

                    <div
                        v-if="asset.isImage || asset.isSvg || asset.isAudio || asset.isVideo"
                        class="editor-preview-image"
                    >
                        <div class="image-wrapper">
                            <!-- Image -->
                            <img v-if="asset.isImage" :src="asset.preview" class="asset-thumb" />

                            <!-- SVG -->
                            <div v-else-if="asset.isSvg" class="bg-checkerboard h-full w-full flex flex-col">
                                <div class="flex border-b-2 border-grey-90">
                                    <div class="flex-1 order-r p-2 border-grey-90 flex items-center justify-center">
                                        <img :src="asset.url" class="asset-thumb w-4 h-4" />
                                    </div>
                                    <div class="flex-1 border-l border-r p-2 border-grey-90 flex items-center justify-center">
                                        <img :src="asset.url" class="asset-thumb w-12 h-12" />
                                    </div>
                                    <div class="flex-1 border-l p-2 border-grey-90 flex items-center justify-center">
                                        <img :src="asset.url" class="asset-thumb w-24 h-24" />
                                    </div>
                                </div>
                                <div class="min-h-0 h-full p-2 flex items-center justify-center">
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

                    <div class="editor-file-actions" v-if="!readOnly">
                        <button v-if="isImage && isFocalPointEditorEnabled" type="button" class="btn" @click.prevent="openFocalPointEditor">
                            {{ __('Set Focal Point') }}
                        </button>

                        <button v-if="canRunAction('rename_asset')" type="button" class="btn" @click.prevent="runAction('rename_asset')">
                            {{ __('Rename') }}
                        </button>

                        <button v-if="canRunAction('move_asset')" type="button" class="btn" @click.prevent="runAction('move_asset')">
                            {{ __('Move') }}
                        </button>

                        <button v-if="canRunAction('replace_asset')" type="button" class="btn" @click.prevent="runAction('replace_asset')">
                            {{ __('Replace') }}
                        </button>

                        <button v-if="canRunAction('reupload_asset')" type="button" class="btn" @click.prevent="runAction('reupload_asset')">
                            {{ __('Reupload') }}
                        </button>
                    </div>

                </div>

                <publish-container
                    v-if="fields"
                    :name="publishContainer"
                    :blueprint="fieldset"
                    :values="values"
                    :meta="meta"
                    :errors="errors"
                    @updated="values = { ...$event, focus: values.focus }"
                >
                    <div class="editor-form" slot-scope="{ setFieldValue, setFieldMeta }">

                        <div v-if="saving" class="loading">
                            <loading-graphic text="Saving" />
                        </div>

                        <div class="editor-form-fields">
                            <div v-if="error" class="bg-red text-white p-2 shadow mb-2" v-text="error" />
                            <publish-fields
                                :fields="fields"
                                :read-only="readOnly"
                                @updated="setFieldValue"
                                @meta-updated="setFieldMeta"
                            />
                        </div>

                        <div class="editor-form-actions text-right" v-if="!readOnly">
                            <button v-if="allowDeleting && canRunAction('delete')" type="button" class="btn-danger mr-1" @click="runAction('delete')">
                                {{ __('Delete') }}
                            </button>
                            <button type="button" class="btn-primary" @click="save">
                                {{ __('Save') }}
                            </button>
                        </div>

                    </div>
                </publish-container>


            </div>

        </template>

        <portal to="outside">
            <focal-point-editor
                v-if="showFocalPointEditor && isFocalPointEditorEnabled"
                :data="values.focus"
                :image="asset.preview"
                @selected="selectFocalPoint"
                @closed="closeFocalPointEditor" />

            <editor-actions
                v-if="actions.length"
                :id="id"
                :actions="actions"
                :url="actionUrl"
                @started="actionStarted"
                @completed="actionCompleted" />
        </portal>

    </div>

    </stack>

</template>


<script>
import EditorActions from './EditorActions.vue';
import FocalPointEditor from './FocalPointEditor.vue';
import PdfViewer from './PdfViewer.vue';
import PublishFields from '../../publish/Fields.vue';
import HasHiddenFields from '../../publish/HasHiddenFields';

export default {

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

        /**
         * Whether the asset is an image
         */
        isImage() {
            if (! this.asset) return false;

            return this.asset.isImage;
        },

        /**
         * Whether there are errors present.
         */
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
        }
    },


    mounted() {
        this.$modal.show('asset-editor');
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
                this.fields = _.chain(this.fieldset.sections)
                    .map(section => section.fields)
                    .flatten(true)
                    .value();

                this.loading = false;
            });
        },

        /**
         * Open the focal point editor UI
         */
        openFocalPointEditor() {
            this.showFocalPointEditor = true;
        },

        /**
         * Close the focal point editor UI
         */
        closeFocalPointEditor() {
            this.showFocalPointEditor = false;
        },

        /**
         * When the focal point is selected
         */
        selectFocalPoint(point) {
            point = (point === '50-50-1') ? null : point;
            this.$set(this.values, 'focus', point);
            this.$dirty.add(this.publishContainer);
        },

        /**
         * Save the asset
         */
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

        /**
         * Close the editor
         */
        close() {
            this.$modal.hide('asset-editor');
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
