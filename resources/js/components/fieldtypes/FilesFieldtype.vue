<template>
    <div class="@container relative">
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
            <div>
                <div
                    v-show="dragging"
                    class="absolute inset-0 flex flex-col gap-2 items-center justify-center bg-white/80 backdrop-blur-sm border border-gray-400 border-dashed rounded-lg"
                >
                    <ui-icon name="upload-cloud" class="size-5 text-gray-500" />
                    <ui-heading size="lg">{{ __('Drop to Upload') }}</ui-heading>
                </div>

                <div class="border border-gray-400 dark:border-gray-700 border-dashed rounded-xl p-4 flex flex-col @2xs:flex-row items-center gap-4" :class="{ 'rounded-b-none': value.length }">
                    <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center flex-1">
                        <ui-icon name="upload-cloud" class="size-5 text-gray-500 me-2" />
                        <span v-text="__('Drag & drop here or&nbsp;')" />
                        <button type="button" class="underline underline-offset-2 cursor-pointer hover:text-black dark:hover:text-gray-200" @click.prevent="uploadFile">
                            {{ __('choose a file') }}
                        </button>
                        <span>.</span>
                    </div>
                </div>

                <div v-if="uploads.length" class="border-gray-300 border-l border-r">
                    <uploads :uploads="uploads" />
                </div>

                <div v-if="value.length" class="relative overflow-hidden rounded-xl border border-gray-300 dark:border-gray-700 border-t-0! rounded-t-none">
                    <table class="w-full">
                        <tbody>
                            <tr
                                v-for="(file, i) in value"
                                :key="file"
                                class="asset-row bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-900"
                            >
                                <td class="flex gap-3 h-full items-center p-3">
                                    <div
                                        class="flex size-7 cursor-pointer items-center justify-center whitespace-nowrap"
                                    >
                                        <file-icon :extension="getExtension(file)" class="size-7" />
                                    </div>
                                    <div
                                        class="flex w-full flex-1 items-center truncate text-sm text-gray-600 dark:text-gray-400 text-start"
                                        v-text="file"
                                    />
                                </td>
                                <td class="p-3 align-middle text-end">
                                    <ui-button
                                        @click="remove(i)"
                                        icon="x"
                                        round
                                        size="xs"
                                        variant="ghost"
                                        :aria-label="__('Remove Asset')"
                                        :title="__('Remove')"
                                    />
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
import { Button } from '@statamic/cms/ui';

export default {
    mixins: [Fieldtype],

    components: {
        Button,
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
