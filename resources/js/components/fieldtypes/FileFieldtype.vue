<template>

    <div class="assets-fieldtype">

        <uploader
            ref="uploader"
            :url="meta.uploadUrl"
            @updated="uploadsUpdated"
            @upload-complete="uploadComplete"
            @error="uploadError"
        >
            <div slot-scope="{ dragging }" class="assets-fieldtype-drag-container">

                <div class="drag-notification" v-show="dragging">
                    <svg-icon name="upload" class="h-8 w-8 mr-3" />
                    <span>{{ __('Drop File to Upload') }}</span>
                </div>

                <div class="assets-fieldtype-picker py-2">
                    <p class="asset-upload-control text-xs text-grey-60 ml-0">
                        <button type="button" class="upload-text-button" @click.prevent="uploadFile">
                            {{ __('Upload file') }}
                        </button>
                        <span class="drag-drop-text" v-text="__('or drag & drop here.')"></span>
                    </p>
                </div>

                <uploads
                    v-if="uploads.length"
                    :uploads="uploads"
                />

            </div>
        </uploader>
    </div>

</template>

<script>
import Uploader from '../assets/Uploader.vue';
import Uploads from '../assets/Uploads.vue';

export default {

    mixins: [Fieldtype],

    components: {
        Uploader,
        Uploads
    },

    data() {
        return {
            uploads: [],
        }
    },

    methods: {

        /**
         * When the uploader component has finished uploading a file.
         */
         uploadComplete(file) {
            this.value.push(file.id);
        },

        /**
         * When the uploader component has modified the uploads array
         */
        uploadsUpdated(uploads) {
            this.uploads = uploads;
        },

        /**
         * When the uploader component encounters an error
         */
        uploadError(upload, uploads) {
            this.uploads = uploads;
            this.$toast.error(upload.errorMessage);
        },

        /**
         * Show the file upload finder window.
         */
        uploadFile() {
            this.$refs.uploader.browse();
        },
    }
}
</script>
