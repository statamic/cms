<template>

    <div class="bg-grey-40 text-xs p-1">
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
export default {

    props: ['uploads'],


    components: {
        Upload: require('./Upload.vue')
    },


    methods: {

        clearUpload(i) {
            this.uploads.splice(i, 1);
        }

    }

}
</script>
