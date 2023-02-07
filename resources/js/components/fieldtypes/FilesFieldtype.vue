<template>

    <div class="assets-fieldtype">

        <uploader
            ref="uploader"
            :url="meta.uploadUrl"
            :container="config.container"
            @updated="uploadsUpdated"
            @upload-complete="uploadComplete"
            @error="uploadError"
        >
            <div slot-scope="{ dragging }" class="assets-fieldtype-drag-container">

                <div class="drag-notification" v-show="dragging">
                    <svg-icon name="upload" class="h-8 w-8 mr-3" />
                    <span>{{ __('Drop File to Upload') }}</span>
                </div>

                <div class="assets-fieldtype-picker py-2" :class="{ 'is-expanded': value.length }">
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

                <div v-if="value.length" class="asset-table-listing">
                    <table class="table-fixed">
                        <tbody>
                            <tr
                                v-for="(file, i) in value"
                                :key="file"
                                class="asset-row bg-white hover:bg-grey-10"
                            >
                                <td class="flex items-center">
                                    <div
                                        class="w-7 h-7 cursor-pointer whitespace-no-wrap flex items-center justify-center"
                                    >
                                        <file-icon :extension="getExtension(file)" />
                                    </div>
                                    <div
                                        class="flex items-center flex-1 ml-1 text-xs text-left truncate"
                                        v-text="file.slice(11)"
                                    />
                                </td>
                                <td class="p-0 w-8 text-right align-middle">
                                    <button
                                        @click="remove(i)"
                                        class="flex items-center p-1 w-full h-full text-grey-60 hover:text-grey-90"
                                    >
                                        <svg-icon name="trash" class="w-6 h-6" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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

        getExtension(file) {
            return file.split('.').pop();
        },

        remove(index) {
            this.update([...this.value.slice(0, index), ...this.value.slice(index + 1)]);
        }
    }
}
</script>
