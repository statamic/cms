<template>

    <div class="bg-gray-400 dark:bg-dark-800 text-xs p-2">
        <upload
            v-for="(upload, i) in uploads"
            :key="upload.id"
            :basename="upload.basename"
            :extension="upload.extension"
            :percent="upload.percent"
            :error="upload.errorMessage"
            @clear="clearUpload(i)"
        />
    </div>

</template>


<script>
import Upload from './Upload.vue';

export default {

    props: ['uploads'],


    components: {
        Upload
    },


    methods: {

        clearUpload(i) {
            this.uploads.splice(i, 1);
        }

    }

}
</script>
