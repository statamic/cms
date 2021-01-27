<template>

    <stack name="asset-editor"
        :before-close="shouldClose"
        @closed="close">

    <div class="asset-editor" :class="isImage ? 'is-image' : 'is-file'">

        <div v-if="loading" class="loading">
            <loading-graphic />
        </div>

        <template v-if="!loading">

            <div class="editor-meta">
                <div class="asset-editor-meta-items">
                    <div class="meta-item">
                        <span class="meta-label">{{ __('Filename') }}</span>
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

                    <div class="editor-preview-image" v-if="isImage">
                        <div class="image-wrapper">
                            <img :src="asset.preview" class="asset-thumb" />
                        </div>
                    </div>

                    <div class="editor-preview-image" v-if="asset.isSvg">
                        <div class="bg-checkerboard h-full w-full">
                            <div class="hidden md:grid md:grid-cols-3 border-b-2 border-grey-90">
                                <div class="border-r p-2 border-grey-90 flex items-center justify-center">
                                    <img :src="asset.url" class="asset-thumb w-4 h-4" />
                                </div>
                                <div class="border-l border-r p-2 border-grey-90 flex items-center justify-center">
                                    <img :src="asset.url" class="asset-thumb w-12 h-12" />
                                </div>
                                <div class="border-l p-2 border-grey-90 flex items-center justify-center">
                                    <img :src="asset.url" class="asset-thumb w-24 h-24" />
                                </div>
                            </div>
                            <div class="h-full flex items-center justify-center">
                                <img :src="asset.url" class="asset-thumb w-2/3 max-h-screen-1/2 relative md:-top-6" />
                            </div>
                        </div>
                    </div>

                    <div class="audio-wrapper" v-if="asset.isAudio">
                        <audio :src="asset.url" controls preload="auto"></audio>
                    </div>

                    <div class="video-wrapper" v-if="asset.isVideo">
                        <video :src="asset.url" controls></video>
                    </div>

                    <div class="h-full" v-if="asset.extension == 'pdf'">
                        <object :data="asset.url" type="application/pdf" width="100%" height="100%">
                        </object>
                    </div>

                    <div class="h-full" v-if="asset.isPreviewable && canUseGoogleDocsViewer">
                        <iframe class="h-full w-full" frameborder="0" :src="'https://docs.google.com/gview?url=' + asset.permalink + '&embedded=true'"></iframe>
                    </div>

                    <div class="editor-file-actions">
                        <button v-if="isImage && isFocalPointEditorEnabled" type="button" class="btn" @click.prevent="openFocalPointEditor">
                            {{ __('Set Focal Point') }}
                        </button>

                        <button v-if="canRunAction('rename_asset')" type="button" class="btn" @click.prevent="runAction('rename_asset')">
                            {{ __('Rename File') }}
                        </button>

                        <button v-if="canRunAction('move_asset')" type="button" class="btn" @click.prevent="runAction('move_asset')">
                            {{ __('Move File') }}
                        </button>

                        <!--
                        <button
                            type="button" class="btn"
                            @click.prevent="replaceFile">Replace File
                        </button>
                        -->
                    </div>

                </div>

                <publish-container
                    v-if="fields"
                    name="publishContainer"
                    :blueprint="fieldset"
                    :values="values"
                    :meta="meta"
                    :errors="errors"
                    @updated="values = $event"
                >
                    <div class="editor-form" slot-scope="{ setFieldValue, setFieldMeta }">

                        <div v-if="saving" class="loading">
                            <loading-graphic text="Saving" />
                        </div>

                        <div class="editor-form-fields">
                            <div v-if="error" class="bg-red text-white p-2 shadow mb-2" v-text="error" />
                            <publish-fields :fields="fields" @updated="setFieldValue" @meta-updated="setFieldMeta" />
                        </div>

                        <div class="editor-form-actions text-right">
                            <button v-if="canRunAction('delete')" type="button" class="btn-danger mr-1" @click="runAction('delete')">
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
                :url="runActionUrl"
                @started="actionStarted"
                @completed="actionCompleted" />
        </portal>

    </div>

    </stack>

</template>


<script>
import EditorActions from './EditorActions.vue';
import FocalPointEditor from './FocalPointEditor.vue';
import PublishFields from '../../publish/Fields.vue';

export default {

    components: {
        EditorActions,
        FocalPointEditor,
        PublishFields,
    },

    props: {
        id: {
            required: true
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

            const url = cp_url(`assets/${btoa(this.id)}`);

            this.$axios.get(url).then(response => {
                const data = response.data.data;
                this.asset = data;
                this.values = data.values;
                this.meta = data.meta;
                this.runActionUrl = data.runActionUrl;
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
            const url = cp_url(`assets/${btoa(this.id)}`);

            this.$axios.patch(url, this.values).then(response => {
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

        actionCompleted(event) {
            this.$events.$emit('editor-action-completed');
            this.close();
        }

    }

}
</script>
