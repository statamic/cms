<template>
    <div class="bg-gray-400 p-2 text-xs dark:bg-dark-800">
        <upload
            v-for="(upload, i) in uploads"
            :key="upload.id"
            :basename="upload.basename"
            :extension="upload.extension"
            :percent="upload.percent"
            :error="upload.errorMessage"
            :error-status="upload.errorStatus"
            :allow-selecting-existing="allowSelectingExisting"
            @clear="clearUpload(i)"
            @retry="retry(i, $event)"
            @existing-selected="existingSelected(i)"
        />
    </div>
</template>

<script>
import Upload from './Upload.vue';

export default {
    props: {
        uploads: Array,
        allowSelectingExisting: Boolean,
    },

    components: {
        Upload,
    },

    methods: {
        clearUpload(i) {
            this.uploads.splice(i, 1);
        },

        retry(i, args) {
            this.uploads[i].retry(args);
        },

        existingSelected(i) {
            this.$emit('existing-selected', this.uploads[i]);
            this.clearUpload(i);
        },
    },
};
</script>
