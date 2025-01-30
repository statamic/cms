<template>

    <div class="assets-fieldtype">

        <uploader
            ref="uploader"
            :url="meta.uploadUrl"
            :extra-data="{ config: configParameter }"
            :container="config.container"
            @updated="uploadsUpdated"
            @upload-complete="uploadComplete"
            @error="uploadError"
            v-slot="{ dragging }"
        >
            <div class="assets-fieldtype-drag-container">

                <div class="drag-notification" v-show="dragging">
                    <svg-icon name="upload" class="h-8 w-8 rtl:ml-6 ltr:mr-6" />
                    <span>{{ __('Drop File to Upload') }}</span>
                </div>

                <div class="assets-fieldtype-picker py-4" :class="{ 'is-expanded': value.length }">
                    <p class="asset-upload-control text-xs text-gray-600 rtl:mr-0 ltr:ml-0">
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
                                class="asset-row bg-white dark:bg-dark-600 hover:bg-gray-100"
                            >
                                <td class="flex items-center">
                                    <div
                                        class="w-7 h-7 cursor-pointer whitespace-nowrap flex items-center justify-center"
                                    >
                                        <file-icon :extension="getExtension(file)" />
                                    </div>
                                    <div
                                        class="flex items-center flex-1 rtl:mr-2 ltr:ml-2 text-xs rtl:text-right ltr:text-left truncate"
                                        v-text="file"
                                    />
                                </td>
                                <td class="p-0 w-8 rtl:text-left ltr:text-right align-middle">
                                    <button
                                        @click="remove(i)"
                                        class="flex items-center p-2 w-full h-full text-gray-600 dark:text-dark-150 hover:text-gray-950 dark:hover:text-dark-100"
                                    >
                                        <svg-icon name="micro/trash" class="w-6 h-6" />
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
import Fieldtype from './Fieldtype.vue';
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

    computed: {
        configParameter() {
            return utf8btoa(JSON.stringify(this.config));
        },
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
