<template>

    <div class="asset-editor-modal">

    <div class="asset-editor {{ isImage ? 'is-image' : 'is-file' }}">

        <div v-if="loading" class="loading">
            <div><span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}</div>
        </div>

        <div v-if="saving" class="loading">
            <div><span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.saving') }}</div>
        </div>

        <template v-if="!loading && !saving">

            <div class="editor-meta">
                <div class="asset-editor-meta-items">
                    <div class="meta-item one-line">
                        <file-icon :extension="asset.extension"></file-icon>
                        {{ asset.path }}
                    </div>
                    <div class="meta-item" v-if="isImage">
                        <span class="meta-label">{{ translate('cp.dimensions') }}</span>
                        <span class="meta-value">{{ asset.width }} x {{ asset.height }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{ translate('cp.size') }}</span>
                        <span class="meta-value">{{ asset.size }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{ translate('cp.last_modified') }}</span>
                        <span class="meta-value" :title="asset.last_modified">{{ asset.last_modified_relative }}</span>
                    </div>
                </div>

                <div class="asset-editor-meta-actions">
                    <a @click.prevent="download" title="{{ translate('cp.download') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="27" height="23" viewBox="0 0 27 23">
                          <g fill="none" fill-rule="evenodd" stroke="#676767" stroke-width="2" transform="translate(1 1.045)">
                            <path d="M21.1219828 6.85714286C21.1219828 6.85714286 20.0297414 6.69642857 18.9655172 6.85714286M3.01724138 6C3.01724138 4.10657143 4.5612069 2.57142857 6.46551724 2.57142857 8.36982759 2.57142857 9.9137931 4.10657143 9.9137931 6"/>
                            <path d="M18.5344828 16.2857143L20.2465517 16.2857143C22.8607759 16.2857143 25 14.1591429 25 11.5594286 25 9.25757143 23.3215517 7.26942857 21.1219828 6.85714286 21.0728448 3.129 18.0219828 0 14.2603448 0 11.8642241 0 9.61465517 1.28785714 8.37241379 3.144 7.82586207 2.78271429 7.17068966 2.57142857 6.46551724 2.57142857 4.5612069 2.57142857 3.01724138 4.10657143 3.01724138 6 3.01724138 6.06557143 3.02327586 6.12985714 3.02715517 6.19457143 3.02284483 6.273 3.01724138 6.35142857 3.01724138 6.42857143 1.29784483 7.248 0 9.19585714 0 11.2092857 0 14.0014286 2.29741379 16.2857143 5.10560345 16.2857143L7.32758621 16.2857143M12.9310345 11.1428571L12.9310345 21M12.9310345 21L9.48275862 17.5714286M16.3793103 17.5714286L12.9310345 21"/>
                          </g>
                        </svg>
                    </a>
                    <a @click.prevent="close" {{ translate('cp.close') }}>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="19" viewBox="0 0 18 19">
                          <g fill="none" fill-rule="evenodd" stroke="#676767" stroke-width="2" transform="translate(1 1.545)">
                            <path d="M16 0L.160533333 15.8389333M16 15.8389333L.160533333 0"/>
                          </g>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="editor-main">

                <div class="editor-preview">

                    <div class="editor-preview-image" v-if="isImage">
                        <div class="image-wrapper">
                            <img :src="asset.preview" class="asset-thumb" />
                        </div>
                    </div>

                    <div class="audio-wrapper" v-if="asset.is_audio">
                        <audio :src="asset.url" controls preload="auto"></audio>
                    </div>

                    <div class="video-wrapper" v-if="asset.is_video">
                        <video :src="asset.url" controls></video>
                    </div>

                    <div class="full-height" v-if="asset.extension == 'pdf'">
                        <object data="{{ asset.url }}" type="application/pdf" width="100%" height="100%">
                        </object>
                    </div>

                    <div class="full-height" v-if="asset.is_previewable">
                        <iframe class="full-height full-width" frameborder="0" src="https://docs.google.com/gview?url={{ asset.permalink }}&embedded=true"></iframe>
                    </div>

                    <div class="editor-file-actions">
                        <button
                            v-if="isImage"
                            type="button" class="btn"
                            @click.prevent="openFocalPointEditor">{{ translate('cp.focal_point') }}
                        </button>

                        <button
                            type="button" class="btn"
                            @click.prevent="openRenamer">{{ translate('cp.rename_file') }}
                        </button>

                        <button
                            type="button" class="btn"
                            @click.prevent="openMover">{{ translate('cp.move_file') }}
                        </button>

                        <!--
                        <button
                            type="button" class="btn"
                            @click.prevent="replaceFile">Replace File
                        </button>
                        -->
                    </div>

                </div>

                <div class="editor-form">

                    <div class="editor-form-fields">
                        <publish-fields
                            :uuid="asset.id"
                            :field-data="fields"
                            :fieldset-name="asset.fieldset"
                            :focus="true">
                        </publish-fields>
                    </div>

                    <div class="editor-form-actions">
                        <button type="button" class="btn btn-danger" @click="delete" v-if="allowDeleting">
                            {{ translate('cp.delete') }}
                        </button>
                        <button type="button" class="btn btn-primary" @click="save">
                            {{ translate('cp.save') }}
                        </button>
                    </div>

                </div>

            </div>

        </template>

        <focal-point-editor
            v-if="showFocalPointEditor"
            :data="fields.focus"
            :image="asset.preview"
            @selected="selectFocalPoint"
            @closed="closeFocalPointEditor">
        </focal-point-editor>

        <image-editor
            v-if="showImageEditor"
            :id="asset.id"
            :container="asset.container"
            :path="asset.path"
            :url="asset.permalink"
            @saved="updateThumbnail">
        </image-editor>

        <renamer
            v-if="showRenamer"
            :asset="asset"
            @saved="assetRenamed"
            @closed="closeRenamer">
        </renamer>

        <mover
            v-if="showMover"
            :assets="[asset.id]"
            :folder="asset.folder"
            :container="asset.container"
            @saved="assetMoved"
            @closed="closeMover">
        </mover>
    </div>

    </div>

</template>


<script>
export default {

    components: {
        FocalPointEditor: require('./FocalPointEditor.vue'),
        Renamer: require('./Renamer.vue'),
        Mover: require('../Mover.vue'),
        PublishFields: require('../../publish/fields'),
    },


    props: {
        id: String,
        hasChild: false,
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
            fields: null,
            showFocalPointEditor: false,
            showRenamer: false,
            showMover: false
        }
    },


    computed: {

        /**
         * Whether the asset is an image
         */
        isImage() {
            if (! this.asset) return false;

            return this.asset.is_image;
        }

    },


    ready() {
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

            const url = cp_url('assets/' + this.id.replace('::', '/'));

            this.$http.get(url).success((response) => {
                this.asset = response.asset;
                this.fields = response.fields;
                this.loading = false;
            });
        },

        /**
         * Open the focal point editor UI
         */
        openFocalPointEditor() {
            this.showFocalPointEditor = true;
            this.hasChild = true;
        },

        /**
         * Close the focal point editor UI
         */
        closeFocalPointEditor() {
            this.showFocalPointEditor = false;
            this.hasChild = false;
        },

        /**
         * When the focal point is selected
         */
        selectFocalPoint(point) {
            point = (point === '50-50') ? null : point;
            this.$set('fields.focus', point);
        },

        /**
         * Save the asset
         */
        save() {
            this.saving = true;

            const url = cp_url('assets/' + this.id.replace('::', '/'));

            this.$http.post(url, this.fields).success((response) => {
                this.$emit('saved', response.asset);
                this.saving = false;
            });

            this.$dispatch('changesMade', false);
        },

        /**
         * Delete the asset
         */
        delete() {
            if (! confirm(translate('cp.are_you_sure'))) {
                return;
            }

            this.saving = true;

            const url = cp_url('assets/delete');

            this.$http.delete(url, { ids: this.asset.id }).success((response) => {
                this.$emit('deleted', this.asset.id);
                this.saving = false;
            });
        },

        /**
         * Close the editor
         */
        close() {
            this.$emit('closed');
        },

        openRenamer() {
            this.showRenamer = true;
            this.hasChild = true;
        },

        closeRenamer() {
            this.showRenamer = false;
            this.hasChild = false;
        },

        assetRenamed(asset) {
            this.asset = asset;
            this.$emit('saved', asset);
        },

        openMover() {
            this.showMover = true;
            this.hasChild = true;
        },

        closeMover() {
            this.showMover = false;
            this.hasChild = false;
        },

        /**
         * When an asset has been moved to another folder
         */
        assetMoved(asset, folder) {
            this.asset = asset;
            this.$emit('moved', asset, folder)
        },

        download() {
            window.open(this.asset.download_url);
        }
    }

}
</script>
