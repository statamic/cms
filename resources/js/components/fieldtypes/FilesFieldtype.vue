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
                    <svg-icon name="upload" class="h-8 w-8 ltr:mr-6 rtl:ml-6" />
                    <span>{{ __('Drop File to Upload') }}</span>
                </div>

                <div class="assets-fieldtype-picker py-4" :class="{ 'is-expanded': value.length }">
                    <p class="asset-upload-control text-xs text-gray-600 ltr:ml-0 rtl:mr-0">
                        <button type="button" class="upload-text-button" @click.prevent="uploadFile">
                            {{ __('Upload file') }}
                        </button>
                        <span class="drag-drop-text" v-text="__('or drag & drop here.')"></span>
                    </p>
                </div>

                <uploads v-if="uploads.length" :uploads="uploads" />

                <div v-if="value.length" class="asset-table-listing">
                    <table class="table-fixed">
                        <tbody>
                            <tr
                                v-for="(file, i) in value"
                                :key="file"
                                class="asset-row dark:bg-dark-600 bg-white hover:bg-gray-100"
                            >
                                <td class="flex items-center">
                                    <div
                                        class="flex h-7 w-7 cursor-pointer items-center justify-center whitespace-nowrap"
                                    >
                                        <file-icon :extension="getExtension(file)" />
                                    </div>
                                    <div
                                        class="flex flex-1 items-center truncate text-xs ltr:ml-2 ltr:text-left rtl:mr-2 rtl:text-right"
                                        v-text="file"
                                    />
                                </td>
                                <td class="w-8 p-0 align-middle ltr:text-right rtl:text-left">
                                    <button
                                        @click="remove(i)"
                                        class="dark:text-dark-150 dark:hover:text-dark-100 flex h-full w-full items-center p-2 text-gray-600 hover:text-gray-950"
                                    >
                                        <svg-icon name="micro/trash" class="h-6 w-6" />
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
        Uploads,
    },

    data() {
        return {
            uploads: [],
        };
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
        },
    },
};
</script>
